<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpReceipt;
use App\Models\PowV1Challenge;
use App\Models\PowV1Commit;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use App\Services\PointCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PowController extends Controller
{
    const CHALLENGE_TTL_SECONDS = 60;
    const MAX_VERIFY_TIME_MS = 5;
    const DEFAULT_PREFIX = '21e8';

    protected $pointCalculationService;

    public function __construct(PointCalculationService $pointCalculationService)
    {
        $this->pointCalculationService = $pointCalculationService;
    }

    public function getParams(Request $request)
    {
        return response()->json([
            'mode' => 'vanity_prefix',
            'default_prefix' => self::DEFAULT_PREFIX,
            'min_miner_version' => 1,
            'suggested_prefix_by_load' => $this->getSuggestedPrefix(),
        ]);
    }

    public function threadBegin(Request $request)
    {
        $validated = $request->validate([
            'post_draft' => 'required|array',
            'post_draft.title' => 'required|string|max:200',
            'post_draft.body' => 'required|string',
            'post_draft.attachments' => 'array',
            'post_draft.refs' => 'array',
            'client_op_id' => 'required|uuid',
        ]);

        // Support both Sanctum and session auth
        $user = $request->user();
        if (!$user && session('bitcoin_auth_id')) {
            $user = \App\Models\BitcoinAuth::find(session('bitcoin_auth_id'));
        }
        
        $publicKey = $user->public_key ?? $user->pubkey_hex ?? "";
        
        // SECURITY: Block dummy/fallback public keys
        if (empty($publicKey) || strlen($publicKey) < 10) {
            return response()->json(['error' => 'Invalid public key'], 401);
        }
        $opId = $validated['client_op_id'];

        $existing = OpReceipt::find($opId);
        if ($existing) {
            return response()->json(json_decode($existing->result_json, true));
        }

        $postDraft = $validated['post_draft'];
        $postJsonMinified = $this->minifyPostJson($postDraft);
        $postBytesHash = hash('sha256', $postJsonMinified, true);

        $timestamp = time();
        $requiredPrefix = $this->determinePrefix($publicKey);

        $canonicalBytes = $this->buildCanonicalBytesV1(
            $publicKey,
            't',
            0,
            0,
            $timestamp,
            $postBytesHash
        );

        $challenge = PowV1Challenge::create([
            'user_pubkey_hex' => $publicKey,
            'scope' => 'thread',
            'thread_id' => 0,
            'parent_id' => 0,
            'post_bytes_hash' => $postBytesHash,
            'required_prefix_hex' => $requiredPrefix,
            'challenge_version' => 1,
            'expires_at' => now()->addSeconds(self::CHALLENGE_TTL_SECONDS),
        ]);

        $result = [
            'challenge_id' => $challenge->id,
            'required_prefix_hex' => $requiredPrefix,
            'challenge_version' => 1,
            'op_id' => $opId,
            'expires_at' => $challenge->expires_at->toIso8601String(),
            'post_bytes_hash' => bin2hex($postBytesHash),
            'canonical_bytes' => bin2hex($canonicalBytes),
        ];

        OpReceipt::create([
            'client_op_id' => $opId,
            'result_json' => json_encode($result),
        ]);

        return response()->json($result);
    }

    public function threadCommit(Request $request)
    {
        $validated = $request->validate([
            'op_id' => 'required|uuid',
            'challenge_id' => 'required|uuid',
            'post_draft' => 'required|array',
            'post_draft.title' => 'required|string|max:200',
            'post_draft.body' => 'required|string',
            'post_draft.attachments' => 'array',
            'post_draft.refs' => 'array',
            'proof' => 'required|array',
            'proof.nonce_u64' => 'required|integer|min:0',
            'proof.miner_version' => 'required|integer|min:1',
            'proof.timestamp_i64' => 'required|integer',
        ]);

        $startTime = microtime(true);
        
        // Support both Sanctum and session auth
        $user = $request->user();
        if (!$user && session('bitcoin_auth_id')) {
            $user = \App\Models\BitcoinAuth::find(session('bitcoin_auth_id'));
        }
        
        $publicKey = $user->public_key ?? $user->pubkey_hex ?? "";
        
        // SECURITY: Block dummy/fallback public keys
        if (empty($publicKey) || strlen($publicKey) < 10) {
            return response()->json(['error' => 'Invalid public key'], 401);
        }
        
        $challenge = PowV1Challenge::findOrFail($validated['challenge_id']);

        if ($challenge->user_pubkey_hex !== $publicKey) {
            return response()->json(['error' => 'Challenge does not belong to user'], 403);
        }

        if ($challenge->expires_at < now()) {
            $this->recordCommit($challenge->id, $validated['proof'], false, 'Challenge expired');
            return response()->json(['error' => 'Challenge expired'], 400);
        }

        $postDraft = $validated['post_draft'];
        $postJsonMinified = $this->minifyPostJson($postDraft);
        $postBytesHash = hash('sha256', $postJsonMinified, true);

        if ($postBytesHash !== $challenge->post_bytes_hash) {
            $this->recordCommit($challenge->id, $validated['proof'], false, 'Post draft mutated');
            return response()->json(['error' => 'Post draft does not match challenge'], 400);
        }

        $canonicalBytes = $this->buildCanonicalBytesV1(
            $challenge->user_pubkey_hex,
            't',
            0,
            0,
            $validated['proof']['timestamp_i64'],
            $postBytesHash
        );

        $isValid = $this->verifyPowV1(
            $canonicalBytes,
            $validated['proof']['nonce_u64'],
            $challenge->required_prefix_hex,
            $solvedHash
        );

        $verifyTimeMs = (int)((microtime(true) - $startTime) * 1000);

        if ($verifyTimeMs > self::MAX_VERIFY_TIME_MS) {
            Log::warning("PoW verification took {$verifyTimeMs}ms (exceeds budget)");
        }

        // SECURITY: Block dummy hash values
        if ($solvedHash === '21e8000000000000000000000000000000000000000000000000000000000000') {
            Log::warning('DUMMY HASH REJECTED', ['hash' => $solvedHash, 'ip' => $request->ip()]);
            return response()->json(['error' => 'Dummy values not accepted'], 400);
        }
        
        // Block suspiciously regular hashes (too many zeros)
        $zeroCount = substr_count($solvedHash, '0');
        if ($zeroCount > 50) {
            Log::warning('SUSPICIOUS HASH PATTERN REJECTED', [
                'hash' => $solvedHash,
                'zero_count' => $zeroCount,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Invalid hash pattern'], 400);
        }

        if (!$isValid) {
            $this->recordCommit($challenge->id, $validated['proof'], false, 'Invalid proof', $solvedHash, $verifyTimeMs);
            return response()->json(['error' => 'Invalid proof'], 400);
        }

        // Calculate and award points
        $points = $this->pointCalculationService->calculatePoints($challenge->required_prefix_hex, $solvedHash);
        
        $thread = DB::transaction(function () use ($user, $postDraft, $challenge, $validated, $solvedHash, $verifyTimeMs, $points) {
            // Create thread
            $thread = Thread::create([
                'board_id' => 1,
                'user_id' => $user->id,
                'title' => $postDraft['title'],
                'body' => $postDraft['body'],
            ]);

            // Award mining points
            $user->awardMiningPoints($points);
            
            Log::info('Thread PoW points awarded', [
                'user_id' => $user->id,
                'username' => $user->username,
                'thread_id' => $thread->id,
                'points_awarded' => $points,
                'total_points' => $user->fresh()->total_pow_points,
                'hash' => $solvedHash,
                'pattern' => $challenge->required_prefix_hex
            ]);

            // Record commit
            $this->recordCommit($challenge->id, $validated['proof'], true, null, $solvedHash, $verifyTimeMs);
            
            return $thread;
        });

        return response()->json([
            'thread_id' => $thread->id,
            'points_awarded' => $points,
            'total_points' => $user->fresh()->total_pow_points
        ]);
    }

    public function replyBegin(Request $request)
    {
        $validated = $request->validate([
            'thread_id' => 'required|integer|exists:threads,id',
            'parent_id' => 'nullable|integer|exists:posts,id',
            'post_draft' => 'required|array',
            'post_draft.body' => 'required|string',
            'post_draft.attachments' => 'array',
            'post_draft.refs' => 'array',
            'client_op_id' => 'required|uuid',
        ]);

        // Support both Sanctum and session auth
        $user = $request->user();
        if (!$user && session('bitcoin_auth_id')) {
            $user = \App\Models\BitcoinAuth::find(session('bitcoin_auth_id'));
        }
        
        $publicKey = $user->public_key ?? $user->pubkey_hex ?? "";
        
        // SECURITY: Block dummy/fallback public keys
        if (empty($publicKey) || strlen($publicKey) < 10) {
            return response()->json(['error' => 'Invalid public key'], 401);
        }
        
        $opId = $validated['client_op_id'];

        $existing = OpReceipt::find($opId);
        if ($existing) {
            return response()->json(json_decode($existing->result_json, true));
        }

        $postDraft = $validated['post_draft'];
        $postDraft['title'] = '';
        $postJsonMinified = $this->minifyPostJson($postDraft);
        $postBytesHash = hash('sha256', $postJsonMinified, true);

        $timestamp = time();
        $requiredPrefix = $this->determinePrefix($publicKey);

        $canonicalBytes = $this->buildCanonicalBytesV1(
            $publicKey,
            'r',
            $validated['thread_id'],
            $validated['parent_id'] ?? 0,
            $timestamp,
            $postBytesHash
        );

        $challenge = PowV1Challenge::create([
            'user_pubkey_hex' => $publicKey,
            'scope' => 'reply',
            'thread_id' => $validated['thread_id'],
            'parent_id' => $validated['parent_id'] ?? 0,
            'post_bytes_hash' => $postBytesHash,
            'required_prefix_hex' => $requiredPrefix,
            'challenge_version' => 1,
            'expires_at' => now()->addSeconds(self::CHALLENGE_TTL_SECONDS),
        ]);

        $result = [
            'challenge_id' => $challenge->id,
            'required_prefix_hex' => $requiredPrefix,
            'challenge_version' => 1,
            'op_id' => $opId,
            'expires_at' => $challenge->expires_at->toIso8601String(),
            'post_bytes_hash' => bin2hex($postBytesHash),
            'canonical_bytes' => bin2hex($canonicalBytes),
        ];

        OpReceipt::create([
            'client_op_id' => $opId,
            'result_json' => json_encode($result),
        ]);

        return response()->json($result);
    }

    public function replyCommit(Request $request)
    {
        Log::info('replyCommit method called');
        $validated = $request->validate([
            'op_id' => 'required|uuid',
            'challenge_id' => 'required|uuid',
            'post_draft' => 'required|array',
            'post_draft.body' => 'required|string',
            'post_draft.attachments' => 'array',
            'post_draft.refs' => 'array',
            'proof' => 'required|array',
            'proof.nonce_u64' => 'required|integer|min:0',
            'proof.miner_version' => 'required|integer|min:1',
            'proof.timestamp_i64' => 'required|integer',
        ]);

        $startTime = microtime(true);
        
        // Support both Sanctum and session auth
        $user = $request->user();
        if (!$user && session('bitcoin_auth_id')) {
            $user = \App\Models\BitcoinAuth::find(session('bitcoin_auth_id'));
        }
        
        $publicKey = $user->public_key ?? $user->pubkey_hex ?? "";
        
        // SECURITY: Block dummy/fallback public keys
        if (empty($publicKey) || strlen($publicKey) < 10) {
            return response()->json(['error' => 'Invalid public key'], 401);
        }
        
        $challenge = PowV1Challenge::findOrFail($validated['challenge_id']);

        if ($challenge->user_pubkey_hex !== $publicKey) {
            return response()->json(['error' => 'Challenge does not belong to user'], 403);
        }

        if ($challenge->expires_at < now()) {
            $this->recordCommit($challenge->id, $validated['proof'], false, 'Challenge expired');
            return response()->json(['error' => 'Challenge expired'], 400);
        }

        $postDraft = $validated['post_draft'];
        $postDraft['title'] = '';
        $postJsonMinified = $this->minifyPostJson($postDraft);
        $postBytesHash = hash('sha256', $postJsonMinified, true);

        if ($postBytesHash !== $challenge->post_bytes_hash) {
            $this->recordCommit($challenge->id, $validated['proof'], false, 'Post draft mutated');
            return response()->json(['error' => 'Post draft does not match challenge'], 400);
        }

        $canonicalBytes = $this->buildCanonicalBytesV1(
            $challenge->user_pubkey_hex,
            'r',
            $challenge->thread_id,
            $challenge->parent_id,
            $validated['proof']['timestamp_i64'],
            $postBytesHash
        );

        $isValid = $this->verifyPowV1(
            $canonicalBytes,
            $validated['proof']['nonce_u64'],
            $challenge->required_prefix_hex,
            $solvedHash
        );

        $verifyTimeMs = (int)((microtime(true) - $startTime) * 1000);

        // SECURITY: Block dummy hash values
        if ($solvedHash === '21e8000000000000000000000000000000000000000000000000000000000000') {
            Log::warning('DUMMY HASH REJECTED', ['hash' => $solvedHash, 'ip' => $request->ip()]);
            return response()->json(['error' => 'Dummy values not accepted'], 400);
        }
        
        // Block suspiciously regular hashes (too many zeros)
        $zeroCount = substr_count($solvedHash, '0');
        if ($zeroCount > 50) {
            Log::warning('SUSPICIOUS HASH PATTERN REJECTED', [
                'hash' => $solvedHash,
                'zero_count' => $zeroCount,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Invalid hash pattern'], 400);
        }

        if (!$isValid) {
            $this->recordCommit($challenge->id, $validated['proof'], false, 'Invalid proof', $solvedHash, $verifyTimeMs);
            return response()->json(['error' => 'Invalid proof'], 400);
        }

        // Calculate and award points
        $points = $this->pointCalculationService->calculatePoints($challenge->required_prefix_hex, $solvedHash);
        
        $post = DB::transaction(function () use ($user, $postDraft, $challenge, $validated, $solvedHash, $verifyTimeMs, $points) {
            // Create post
            $post = Post::create([
                'thread_id' => $challenge->thread_id,
                'parent_id' => $challenge->parent_id,
                'user_id' => $user->id,
                'body' => $postDraft['body'],
            ]);

            // Award mining points
            $user->awardMiningPoints($points);
            
            Log::info('Reply PoW points awarded', [
                'user_id' => $user->id,
                'username' => $user->username,
                'post_id' => $post->id,
                'thread_id' => $challenge->thread_id,
                'points_awarded' => $points,
                'total_points' => $user->fresh()->total_pow_points,
                'hash' => $solvedHash,
                'pattern' => $challenge->required_prefix_hex
            ]);

            // Record commit
            $this->recordCommit($challenge->id, $validated['proof'], true, null, $solvedHash, $verifyTimeMs);
            
            return $post;
        });

        return response()->json([
            'post_id' => $post->id,
            'points_awarded' => $points,
            'total_points' => $user->fresh()->total_pow_points
        ]);
    }

    private function buildCanonicalBytesV1($userPubkeyHex, $scope, $threadId, $parentId, $timestamp, $postBytesHash)
    {
        $bytes = 'HC1';
        $bytes .= $userPubkeyHex;
        $bytes .= $scope;
        $bytes .= pack('P', $threadId);
        $bytes .= pack('P', $parentId);
        $bytes .= pack('q', $timestamp);
        $bytes .= $postBytesHash;
        return $bytes;
    }

    private function verifyPowV1($canonicalBytes, $nonce, $requiredPrefix, &$solvedHash)
    {
        $powInput = $canonicalBytes . pack('P', $nonce);
        $hash = hash('sha256', $powInput, false);
        $solvedHash = $hash;
        return str_starts_with($hash, $requiredPrefix);
    }

    private function minifyPostJson($postDraft)
    {
        $sorted = [
            'attachments' => $postDraft['attachments'] ?? [],
            'body' => $postDraft['body'],
            'refs' => $postDraft['refs'] ?? [],
            'title' => $postDraft['title'] ?? '',
        ];
        return json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function determinePrefix($userPubkeyHex)
    {
        return self::DEFAULT_PREFIX;
    }

    private function getSuggestedPrefix()
    {
        return self::DEFAULT_PREFIX;
    }

    private function recordCommit($challengeId, $proof, $accepted, $rejectReason = null, $solvedHash = null, $solveTimeMs = null)
    {
        PowV1Commit::create([
            'challenge_id' => $challengeId,
            'nonce_u64' => $proof['nonce_u64'],
            'miner_version' => $proof['miner_version'],
            'timestamp_i64' => $proof['timestamp_i64'],
            'solved_hash_hex' => $solvedHash ?? '',
            'accepted' => $accepted,
            'reject_reason' => $rejectReason,
            'solve_time_ms' => $solveTimeMs,
        ]);
    }
}
