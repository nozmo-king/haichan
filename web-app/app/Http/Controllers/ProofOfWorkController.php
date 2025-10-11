<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\ProofOfWork;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProofOfWorkController extends Controller
{
    public function submitProof(Request $request)
    {
        Log::info('=== PROOF SUBMISSION RECEIVED ===', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $validator = Validator::make($request->all(), [
            'challenge_token' => 'required|string',
            'client_nonce' => 'required', // Accept number or string
            'hash' => 'required|string|size:64',
        ]);

        if ($validator->fails()) {
            Log::error('PROOF VALIDATION FAILED', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof format: challenge_token, client_nonce, and hash are required',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        Log::info('PROOF VALIDATION PASSED, starting verification', [
            'challenge_token' => $request->input('challenge_token'),
            'hash' => $request->input('hash'),
            'nonce' => $request->input('client_nonce')
        ]);

        $verifier = new \App\Services\ChallengeVerifier();
        $verificationResult = $verifier->verifyChallenge(
            $request->input('challenge_token'),
            $request->input('client_nonce'),
            $request->input('hash')
        );

        if (!$verificationResult['valid']) {
            Log::error('CHALLENGE VERIFICATION FAILED', [
                'token' => $request->input('challenge_token'),
                'error' => $verificationResult['error'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $verificationResult['error'],
                'details' => $verificationResult,
            ], 400);
        }

        $challenge = $verificationResult['challenge'];

        if (ProofOfWork::where('hash', $request->input('hash'))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate proof',
            ], 400);
        }

        // SECURITY: Block dummy/fallback values
        $hash = $request->input('hash');
        if ($hash === '21e8000000000000000000000000000000000000000000000000000000000000') {
            Log::warning('DUMMY HASH REJECTED', [
                'hash' => $hash,
                'ip' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Dummy values not accepted',
            ], 400);
        }

        $points = $this->calculatePoints($challenge->difficulty);

        $threadId = null;
        if ($challenge->target_type === 'thread') {
            $threadId = $challenge->target_id;
        } elseif ($challenge->target_type === 'reply') {
            $threadId = $challenge->target_id;
        }

        $challenge->markAsUsed();

        // Use database transaction with retry for concurrent proof submissions
        $proofOfWork = null;
        $maxRetries = 3;
        $retries = 0;
        
        while ($retries < $maxRetries) {
            try {
                $proofOfWork = \DB::transaction(function () use ($challenge, $threadId, $request, $points) {
                    return ProofOfWork::create([
                        'challenge_id' => $challenge->id,
                        'thread_id' => $threadId,
                        'hash' => $request->input('hash'),
                        'nonce' => 0,
                        'data' => json_encode($challenge->canonical_payload),
                        'pattern' => $challenge->difficulty,
                        'points' => $points,
                        'ip_address' => $request->ip(),
                        'verified_at' => now(),
                        'user_id' => $challenge->user_id,
                    ]);
                });
                break; // Success, exit retry loop
            } catch (\Exception $e) {
                $retries++;
                if ($retries >= $maxRetries) {
                    Log::error('Failed to save proof after retries', [
                        'error' => $e->getMessage(),
                        'hash' => $request->input('hash')
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Database busy, try again'
                    ], 500);
                }
                // Wait a bit before retrying
                usleep(100000); // 100ms
            }
        }

        if ($threadId) {
            $thread = Thread::find($threadId);
            if ($thread) {
                $thread->increment('bump_score', $points);
                $thread->update(['bumped_at' => now()]);

                // Clear board cache so PoW numbers update immediately
                $cacheKey = "board_threads_{$thread->board_id}";
                \Cache::forget($cacheKey);

                if ($thread->board && method_exists($thread->board, 'addPowPoints')) {
                    $thread->board->addPowPoints($points);
                }

                Log::info('THREAD BUMPED WITH POW', [
                    'thread_id' => $threadId,
                    'points_added' => $points,
                    'new_bump_score' => $thread->fresh()->bump_score,
                    'pattern' => $challenge->difficulty,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Proof accepted!',
            'points' => $points,
            'total_points' => $points,
            'challenge_id' => $challenge->id,
        ]);
    }

    public function bumpThread(Request $request, $boardName, $threadId)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800,21e8000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof format',
            ], 422);
        }

        $board = Board::where('name', $boardName)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);

        $verificationResult = $this->verifyProof(
            $request->input('data'),
            $request->input('nonce'),
            $request->input('hash'),
            $request->input('pattern')
        );

        if (! $verificationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verificationResult['error'],
            ], 400);
        }

        $points = $this->calculatePoints($request->input('pattern'));

        ProofOfWork::create([
            'thread_id' => $threadId,
            'hash' => $request->input('hash'),
            'nonce' => $request->input('nonce'),
            'data' => $request->input('data'),
            'pattern' => $request->input('pattern'),
            'points' => $points,
            'verified_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $thread->increment('bump_score', $points);
        $thread->update(['bumped_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Thread bumped successfully',
            'points' => $points,
            'thread_bump_score' => $thread->bump_score,
        ]);
    }

    public function verifyProof($data, $nonce, $submittedHash, $pattern)
    {
        Log::info('=== PROOF VERIFICATION WITH SERVER CHECK ===', [
            'data' => $data,
            'nonce' => $nonce,
            'submitted_hash' => $submittedHash,
            'pattern' => $pattern,
        ]);

        // First verify the submitted hash matches the pattern
        if (! str_starts_with(strtolower($submittedHash), strtolower($pattern))) {
            Log::error('PATTERN MISMATCH', [
                'submitted_hash' => $submittedHash,
                'pattern' => $pattern,
                'hash_start' => substr($submittedHash, 0, 10),
            ]);

            return ['valid' => false, 'error' => 'Hash does not match the expected pattern.'];
        }

        // Check for duplicate hash
        if (ProofOfWork::where('hash', $submittedHash)->exists()) {
            return ['valid' => false, 'error' => 'Duplicate proof'];
        }

        // Server-side verification: recompute hash and verify
        // The client already includes nonce in data, so use data directly
        $serverHash = hash('sha256', $data);

        Log::info('SERVER HASH VERIFICATION', [
            'data' => $data,
            'server_hash' => $serverHash,
            'client_hash' => $submittedHash,
            'hashes_match' => $serverHash === $submittedHash,
        ]);

        // Strict hash validation - no fallback allowed in production
        if ($serverHash !== $submittedHash) {
            Log::error('HASH VERIFICATION FAILED', [
                'server_sha256' => $serverHash,
                'client_submitted' => $submittedHash,
                'data' => $data,
            ]);

            return ['valid' => false, 'error' => 'Hash verification failed - server computed: '.$serverHash];
        }

        Log::info('PROOF VERIFIED SUCCESSFULLY', [
            'hash' => $submittedHash,
            'verification_type' => 'SHA256',
        ]);

        return ['valid' => true];
    }

    private function calculatePoints($pattern)
    {
        $points = [
            // Standard patterns - FIXED SCORING
            '21' => 0.1, // Idle pattern - very low points
            '21e' => 0.5, // Easy pattern for replies
            '21e8' => 100, // MAIN MINING PATTERN - 100 POINTS
            '21e80' => 500, // 5x harder
            '21e800' => 2500, // 25x harder
            '21e8000' => 10000, // 100x harder
            '000021e8' => 50000, // Ultra rare

            // Legendary patterns
            '000' => 500,  // Triple zero
            '111' => 400,  // Triple one
            '222' => 300,
            '333' => 350,
            '444' => 300,
            '555' => 450,  // Lucky fives
            '666' => 666,  // Devil number - high value
            '777' => 777,  // Lucky sevens - highest
            '888' => 400,  // Lucky eights
            '999' => 350,

            // Hex letter patterns
            'aaa' => 250,
            'bbb' => 250,
            'ccc' => 250,
            'ddd' => 250,
            'eee' => 250,
            'fff' => 300,  // All F's

            // 3-letter vanity words
            'ace' => 150,  // Ace
            'bad' => 100,  // Bad
            'cab' => 80,   // Cab
            'dad' => 120,  // Dad
            'ded' => 200,  // Ded (dead misspelled)
            'fab' => 100,  // Fab
            'fed' => 90,   // Fed

            // 4-letter vanity words
            'beef' => 300,  // Beef
            'cafe' => 250,  // Cafe
            'face' => 200,  // Face
            'babe' => 180,  // Babe
            'fade' => 150,  // Fade
            'dead' => 400,  // Dead
            'deed' => 250,  // Deed
            'feed' => 200,  // Feed

            // Internet culture
            'deadbeef' => 3133, // DEADBEEF - legendary 8-char hex pattern
            'c0de' => 1337, // Code - elite value
            'b00b' => 800,  // Boob - high meme value
            '1337' => 1337, // Leet - ultimate
            'pwnd' => 500,  // Pwned
            'rekt' => 400,  // Rekt
            'epic' => 300,  // Epic
            'chad' => 250,  // Chad (case insensitive)
            'Chad' => 250,   // Chad (proper case)
        ];

        return $points[$pattern] ?? 0.1;
    }


    public function postBump(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
            'hash' => 'required|string|size:64',
            'multiplier' => 'required|integer|min:1|max:100',
            'thread_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bump request',
            ], 422);
        }

        $hash = $request->input('hash');
        $multiplier = $request->input('multiplier');
        $threadId = $request->input('thread_id');
        $postId = $request->input('post_id');

        // Verify hash starts with 21e8
        if (! str_starts_with(strtolower($hash), '21e8')) {
            return response()->json([
                'success' => false,
                'message' => 'Hash does not start with 21e8',
            ], 400);
        }

        // Check if this hash bump has already been processed
        $existingBump = ProofOfWork::where('hash', $hash)
            ->where('data', 'LIKE', "post_bump:{$postId}:%")
            ->first();

        if ($existingBump) {
            return response()->json([
                'success' => false,
                'message' => 'Bump already processed for this hash',
            ], 400);
        }

        // Calculate bump points
        $basePoints = 1;
        if (str_starts_with($hash, '21e8000')) {
            $basePoints = 25;
        } elseif (str_starts_with($hash, '21e800')) {
            $basePoints = 5;
        } elseif (str_starts_with($hash, '21e80')) {
            $basePoints = 2;
        }

        $bumpPoints = $basePoints * $multiplier;

        // Create ProofOfWork record for the bump
        ProofOfWork::create([
            'thread_id' => $threadId,
            'hash' => $hash,
            'nonce' => 0, // Post bumps don't use nonce
            'data' => "post_bump:{$postId}:{$hash}",
            'pattern' => $this->detectPattern($hash),
            'points' => $bumpPoints,
            'ip_address' => $request->ip(),
            'verified_at' => now(),
        ]);

        // Apply bump to thread
        $thread = Thread::find($threadId);
        if ($thread) {
            $thread->increment('bump_score', $bumpPoints);
            $thread->update(['bumped_at' => now()]);
        }

        Log::info('21e8 POST BUMP APPLIED', [
            'post_id' => $postId,
            'thread_id' => $threadId,
            'hash' => $hash,
            'bump_points' => $bumpPoints,
            'multiplier' => $multiplier,
        ]);

        return response()->json([
            'success' => true,
            'message' => '21e8 bump applied successfully!',
            'bump_points' => $bumpPoints,
            'thread_bump_score' => $thread->bump_score ?? 0,
        ]);
    }

    private function detectPattern($hash)
    {
        $hash = strtolower($hash);
        if (str_starts_with($hash, '21e8000')) {
            return '21e8000';
        }
        if (str_starts_with($hash, '21e800')) {
            return '21e800';
        }
        if (str_starts_with($hash, '21e80')) {
            return '21e80';
        }
        if (str_starts_with($hash, '21e8')) {
            return '21e8';
        }

        return '21';
    }

    public function getStats()
    {
        // Get real network statistics
        $totalProofs = ProofOfWork::count();
        $recentProofs = ProofOfWork::where('created_at', '>', now()->subHours(24))->count();
        $activeSessions = \App\Models\MiningSession::where('updated_at', '>', now()->subMinutes(10))->count();

        // Top patterns found recently
        $topPatterns = ProofOfWork::select('pattern', \DB::raw('count(*) as count'), \DB::raw('sum(points) as total_points'))
            ->where('created_at', '>', now()->subDays(7))
            ->groupBy('pattern')
            ->orderBy('total_points', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_proofs' => $totalProofs,
            'recent_proofs_24h' => $recentProofs,
            'active_miners' => max(1, $activeSessions), // At least show 1 (the current user)
            'session_proofs' => ProofOfWork::where('ip_address', request()->ip())
                ->where('created_at', '>', now()->subHours(1))
                ->count(),
            'top_patterns' => $topPatterns,
            'network_hashrate' => $this->estimateNetworkHashrate(),
            'total_points_awarded' => ProofOfWork::sum('points'),
        ]);
    }

    private function estimateNetworkHashrate()
    {
        // Estimate network hashrate based on recent proof submissions
        $recentProofs = ProofOfWork::where('created_at', '>', now()->subMinutes(10))->count();

        // Rough estimate: if we got X proofs in 10 minutes,
        // and average proof takes ~256 hashes, then hashrate is approximately:
        $estimatedHashes = $recentProofs * 256; // Average for '21e8' pattern
        $estimatedHashrate = $estimatedHashes / 600; // 600 seconds = 10 minutes

        return round($estimatedHashrate);
    }

    public function startMiningSession(Request $request)
    {
        // For now, just return success - could track session data later
        return response()->json([
            'status' => 'success',
            'message' => 'Mining session started',
            'session_id' => uniqid(),
            'timestamp' => time(),
        ]);
    }

    public function endMiningSession(Request $request)
    {
        // For now, just return success - could track session stats later
        return response()->json([
            'status' => 'success',
            'message' => 'Mining session ended',
            'timestamp' => time(),
        ]);
    }
}
