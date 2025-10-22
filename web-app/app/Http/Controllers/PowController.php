<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PowChallenge;
use App\Models\PowCommit;
use App\Models\Post;
use App\Models\OpReceipt;
use Carbon\Carbon;

// Assuming these are available from the Rust verifier library or a PHP port
// For now, we'll re-implement the core logic here.
use App\Services\PowVerifierFFI;

class PowController extends Controller
{
    const CHALLENGE_VERSION = 1;
    const DEFAULT_REQUIRED_PREFIX_HEX = "21e8";
    const CHALLENGE_TTL_SECONDS = 60;
    const MIN_MINER_VERSION = 1;

    protected PowVerifierFFI $powVerifierFFI;

    public function __construct(PowVerifierFFI $powVerifierFFI)
    {
        $this->powVerifierFFI = $powVerifierFFI;
    }

    private function canonicalBytesV1(
        string $user_pubkey_hex,
        string $scope,
        ?int $thread_id,
        ?int $parent_id,
        int $timestamp_i64,
        string $post_bytes_hash // raw bytes
    ): string {
        $bytes = "HC1";
        $bytes .= $user_pubkey_hex;
        $bytes .= $scope;
        $bytes .= pack('J', $thread_id ?? 0);
        $bytes .= pack('J', $parent_id ?? 0);
        $bytes .= pack('J', $timestamp_i64);
        $bytes .= $post_bytes_hash;
        return $bytes;
    }

    private function powInputV1(string $canonical_bytes, int $nonce_u64): string
    {
        return $canonical_bytes . pack('P', $nonce_u64); // Little-endian u64
    }

    private function hashInput(string $input): string
    {
        return hash('sha256', $input, true);
    }

    public function getPowParams(Request $request)
    {
        return response()->json([
            'mode' => 'vanity_prefix',
            'default_prefix' => self::DEFAULT_REQUIRED_PREFIX_HEX,
            'min_miner_version' => self::MIN_MINER_VERSION,
            'suggested_prefix_by_load' => self::DEFAULT_REQUIRED_PREFIX_HEX, // TODO: Implement dynamic load-based prefix
        ]);
    }

    public function beginPost(Request $request, string $scope)
    {
        $request->validate([
            'post_draft' => 'required|array',
            'client_op_id' => 'required|uuid',
            'thread_id' => 'nullable|uuid',
            'parent_id' => 'nullable|uuid',
        ]);

        $clientOpId = $request->input('client_op_id');

        // Check for idempotency
        $existingReceipt = OpReceipt::find($clientOpId);
        if ($existingReceipt) {
            return response()->json(json_decode($existingReceipt->result_json), 200);
        }

        $userPubkeyHex = $request->header('X-Pubkey'); // Assuming pubkey is in header
        if (!$userPubkeyHex) {
            return response()->json(['error' => 'X-Pubkey header missing'], 401);
        }

        // Ensure user exists or create a new one (for demo purposes)
        $user = User::firstOrCreate(['pubkey_hex' => $userPubkeyHex]);

        $postDraft = PostDraft::fromArray($request->input('post_draft'));
        $postBytesHash = $postDraft->calculatePostBytesHash();

        $requiredPrefixHex = self::DEFAULT_REQUIRED_PREFIX_HEX; // TODO: Implement dynamic difficulty

        $threadId = $request->input('thread_id') ? (int) $request->input('thread_id') : null;
        $parentId = $request->input('parent_id') ? (int) $request->input('parent_id') : null;

        $challenge = PowChallenge::create([
            'user_pubkey_hex' => $userPubkeyHex,
            'scope' => $scope,
            'thread_id' => $threadId,
            'parent_id' => $parentId,
            'post_bytes_hash' => $postBytesHash,
            'required_prefix_hex' => $requiredPrefixHex,
            'challenge_version' => self::CHALLENGE_VERSION,
            'expires_at' => Carbon::now()->addSeconds(self::CHALLENGE_TTL_SECONDS),
        ]);

        $response = [
            'challenge_id' => $challenge->id,
            'required_prefix_hex' => $requiredPrefixHex,
            'challenge_version' => self::CHALLENGE_VERSION,
            'op_id' => $clientOpId,
            'expires_at' => $challenge->expires_at->timestamp,
            'post_bytes_hash' => bin2hex($postBytesHash),
        ];

        OpReceipt::create([
            'client_op_id' => $clientOpId,
            'result_json' => json_encode($response),
        ]);

        return response()->json($response);
    }

    public function commitPost(Request $request, string $scope)
    {
        $request->validate([
            'op_id' => 'required|uuid',
            'challenge_id' => 'required|uuid',
            'post_draft' => 'required|array',
            'proof' => 'required|array',
            'proof.nonce_u64' => 'required|integer',
            'proof.miner_version' => 'required|integer',
            'proof.timestamp_i64' => 'required|integer',
        ]);

        $clientOpId = $request->input('op_id');

        // Check for idempotency
        $existingReceipt = OpReceipt::find($clientOpId);
        if ($existingReceipt) {
            return response()->json(json_decode($existingReceipt->result_json), 200);
        }

        $challenge = PowChallenge::find($request->input('challenge_id'));

        if (!$challenge) {
            return response()->json(['error' => 'Challenge not found'], 404);
        }

        if ($challenge->expires_at->isPast()) {
            $this->recordPowCommit($challenge, $request, false, 'Challenge expired');
            return response()->json(['error' => 'Challenge expired'], 400);
        }

        $userPubkeyHex = $request->header('X-Pubkey');
        if ($userPubkeyHex !== $challenge->user_pubkey_hex) {
            $this->recordPowCommit($challenge, $request, false, 'Pubkey mismatch');
            return response()->json(['error' => 'Pubkey mismatch'], 403);
        }

        $postDraft = PostDraft::fromArray($request->input('post_draft'));
        $currentPostBytesHash = $postDraft->calculatePostBytesHash();

        if ($currentPostBytesHash !== $challenge->post_bytes_hash) {
            $this->recordPowCommit($challenge, $request, false, 'Post draft mutated');
            return response()->json(['error' => 'Post draft mutated'], 400);
        }

        $proof = $request->input('proof');
        $nonceU64 = $proof['nonce_u64'];
        $minerVersion = $proof['miner_version'];
        $timestampI64 = $proof['timestamp_i64'];

        if ($minerVersion < self::MIN_MINER_VERSION) {
            $this->recordPowCommit($challenge, $request, false, 'Miner version too low');
            return response()->json(['error' => 'Miner version too low'], 400);
        }

        $canonicalBytes = $this->canonicalBytesV1(
            $challenge->user_pubkey_hex,
            $challenge->scope,
            $challenge->thread_id,
            $challenge->parent_id,
            $timestampI64,
            $challenge->post_bytes_hash
        );

        $startTime = microtime(true);
        $isPowValid = $this->powVerifierFFI->verifyPowV1(
            $canonicalBytes . pack('P', $nonceU64),
            $challenge->required_prefix_hex
        );
        $endTime = microtime(true);
        $solveTimeMs = (int) (($endTime - $startTime) * 1000);

        $solvedHashHex = null;
        if ($isPowValid) {
            $input = $canonicalBytes . pack('P', $nonceU64);
            $solvedHashHex = bin2hex(hash('sha256', $input, true));
        }

        if (!$isPowValid) {
            $this->recordPowCommit($challenge, $request, false, 'Invalid PoW solution', $solveTimeMs);
            return response()->json(['error' => 'Invalid PoW solution'], 400);
        }

        // PoW is valid, create the post
        $post = Post::create([
            'thread_id' => $challenge->thread_id,
            'parent_id' => $challenge->parent_id,
            'author_pubkey_hex' => $challenge->user_pubkey_hex,
            'title' => $postDraft->title,
            'body' => $postDraft->body,
            'attachments_json' => json_encode($postDraft->attachments),
        ]);

        $this->recordPowCommit($challenge, $request, true, null, $solveTimeMs, $solvedHashHex);

        $response = [
            'thread_id' => $post->thread_id ?? $post->id,
        ];

        OpReceipt::create([
            'client_op_id' => $clientOpId,
            'result_json' => json_encode($response),
        ]);

        return response()->json($response);
    }

    private function recordPowCommit(
        PowChallenge $challenge,
        Request $request,
        bool $accepted,
        ?string $rejectReason,
        ?int $solveTimeMs = null,
        ?string $solvedHashHex = null
    ): void {
        $proof = $request->input('proof');
        PowCommit::create([
            'id' => (string) Str::uuid(),
            'challenge_id' => $challenge->id,
            'nonce_u64' => $proof['nonce_u64'],
            'miner_version' => $proof['miner_version'],
            'timestamp_i64' => $proof['timestamp_i64'],
            'solved_hash_hex' => $solvedHashHex ?? '',
            'accepted' => $accepted,
            'reject_reason' => $rejectReason,
            'solve_time_ms' => $solveTimeMs,
        ]);
    }

    public function beginThread(Request $request) { return $this->beginPost($request, 'thread'); }
    public function commitThread(Request $request) { return $this->commitPost($request, 'thread'); }
    public function beginReply(Request $request) { return $this->beginPost($request, 'reply'); }
    public function commitReply(Request $request) { return $this->commitPost($request, 'reply'); }
}
