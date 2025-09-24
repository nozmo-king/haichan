<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Board;
use App\Models\Thread;
use App\Models\ProofOfWork;

class ProofOfWorkController extends Controller
{
    public function submitProof(Request $request)
    {
        Log::info('=== PROOF SUBMISSION RECEIVED ===', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800,21e8000,000021e8',
            'target_type' => 'nullable|string',
            'target_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid proof format'
            ], 422);
        }

        $verificationResult = $this->verifyProof(
            $request->input('data'),
            $request->input('nonce'),
            $request->input('hash'),
            $request->input('pattern')
        );

        if (!$verificationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verificationResult['error']
            ], 400);
        }

        $points = $this->calculatePoints($request->input('pattern'));

        // Link proof to thread if target is thread-related  
        $threadId = null;
        if ($request->input('target_type') === 'thread') {
            $threadId = $request->input('target_id');
        } elseif ($request->input('target_type') === 'reply') {
            // For reply mining, get the thread ID from the data string or request
            // The reply mining sends thread context data
            $dataString = $request->input('data');
            if (preg_match('/thread-(\d+)/', $dataString, $matches)) {
                $threadId = $matches[1];
            }
        }

        ProofOfWork::create([
            'thread_id' => $threadId,
            'hash' => $request->input('hash'),
            'nonce' => $request->input('nonce'),
            'data' => $request->input('data'),
            'pattern' => $request->input('pattern'),
            'points' => $points,
            'ip_address' => $request->ip(),
            'verified_at' => now()
        ]);

        // Add PoW points to the board if this is thread-related mining
        if ($threadId) {
            $thread = Thread::find($threadId);
            if ($thread && $thread->board) {
                $thread->board->addPowPoints($points);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Proof accepted!',
            'points' => $points,
            'total_points' => $points
        ]);
    }

    public function bumpThread(Request $request, $boardName, $threadId)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21,21e8,21e80,21e800'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof format'
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

        if (!$verificationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verificationResult['error']
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
            'ip_address' => $request->ip()
        ]);

        $thread->increment('bump_score', $points);
        $thread->update(['bumped_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Thread bumped successfully',
            'points' => $points,
            'thread_bump_score' => $thread->bump_score
        ]);
    }

    public function verifyProof($data, $nonce, $submittedHash, $pattern)
    {
        Log::info('=== PROOF VERIFICATION WITH SERVER CHECK ===', [
            'data' => $data,
            'nonce' => $nonce,
            'submitted_hash' => $submittedHash,
            'pattern' => $pattern
        ]);

        // First verify the submitted hash matches the pattern
        if (!str_starts_with(strtolower($submittedHash), strtolower($pattern))) {
            Log::error('PATTERN MISMATCH', [
                'submitted_hash' => $submittedHash,
                'pattern' => $pattern,
                'hash_start' => substr($submittedHash, 0, 10)
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
            'hashes_match' => $serverHash === $submittedHash
        ]);

        // Strict hash validation - no fallback allowed in production
        if ($serverHash !== $submittedHash) {
            Log::error('HASH VERIFICATION FAILED', [
                'server_sha256' => $serverHash,
                'client_submitted' => $submittedHash,
                'data' => $data
            ]);
            return ['valid' => false, 'error' => 'Hash verification failed - server computed: ' . $serverHash];
        }

        Log::info('PROOF VERIFIED SUCCESSFULLY', [
            'hash' => $submittedHash,
            'verification_type' => 'SHA256'
        ]);
        
        return ['valid' => true];
    }


    private function calculatePoints($pattern)
    {
        $points = [
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 100,
            '000021e8' => 625
        ];
        return $points[$pattern] ?? 0.1;
    }

    public function postBump(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
            'hash' => 'required|string|size:64',
            'multiplier' => 'required|integer|min:1|max:100',
            'thread_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bump request'
            ], 422);
        }

        $hash = $request->input('hash');
        $multiplier = $request->input('multiplier');
        $threadId = $request->input('thread_id');
        $postId = $request->input('post_id');

        // Verify hash starts with 21e8
        if (!str_starts_with(strtolower($hash), '21e8')) {
            return response()->json([
                'success' => false,
                'message' => 'Hash does not start with 21e8'
            ], 400);
        }

        // Check if this hash bump has already been processed
        $existingBump = ProofOfWork::where('hash', $hash)
            ->where('data', 'LIKE', "post_bump:{$postId}:%")
            ->first();

        if ($existingBump) {
            return response()->json([
                'success' => false,
                'message' => 'Bump already processed for this hash'
            ], 400);
        }

        // Calculate bump points
        $basePoints = 1;
        if (str_starts_with($hash, '21e8000')) $basePoints = 25;
        elseif (str_starts_with($hash, '21e800')) $basePoints = 5;
        elseif (str_starts_with($hash, '21e80')) $basePoints = 2;
        
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
            'verified_at' => now()
        ]);

        // Apply bump to thread
        $thread = Thread::find($threadId);
        if ($thread) {
            $thread->increment('bump_score', $bumpPoints);
            $thread->update(['bumped_at' => now()]);
        }

        Log::info("21e8 POST BUMP APPLIED", [
            'post_id' => $postId,
            'thread_id' => $threadId,
            'hash' => $hash,
            'bump_points' => $bumpPoints,
            'multiplier' => $multiplier
        ]);

        return response()->json([
            'success' => true,
            'message' => '21e8 bump applied successfully!',
            'bump_points' => $bumpPoints,
            'thread_bump_score' => $thread->bump_score ?? 0
        ]);
    }

    private function detectPattern($hash)
    {
        $hash = strtolower($hash);
        if (str_starts_with($hash, '21e8000')) return '21e8000';
        if (str_starts_with($hash, '21e800')) return '21e800';
        if (str_starts_with($hash, '21e80')) return '21e80';
        if (str_starts_with($hash, '21e8')) return '21e8';
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
            'total_points_awarded' => ProofOfWork::sum('points')
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
            'timestamp' => time()
        ]);
    }

    public function endMiningSession(Request $request)
    {
        // For now, just return success - could track session stats later
        return response()->json([
            'status' => 'success', 
            'message' => 'Mining session ended',
            'timestamp' => time()
        ]);
    }

}
