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
            'pattern' => 'required|string|in:21,21e8,21e80,21e800,21e8000,000021e8'
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
        $calculatedHash = hash('sha256', $data . ':' . $nonce);

        Log::info('=== PROOF VERIFICATION ===', [
            'data' => $data,
            'nonce' => $nonce,
            'submitted_hash' => $submittedHash,
            'calculated_hash' => $calculatedHash,
            'pattern' => $pattern,
            'hash_starts_with' => substr($calculatedHash, 0, 10),
            'pattern_match' => str_starts_with(strtolower($calculatedHash), strtolower($pattern))
        ]);

        if ($calculatedHash !== strtolower($submittedHash)) {
            Log::error('HASH MISMATCH', ['calculated' => $calculatedHash, 'submitted' => $submittedHash]);
            return ['valid' => false, 'error' => 'Hash mismatch'];
        }

        if (!str_starts_with(strtolower($calculatedHash), strtolower($pattern))) {
            Log::error('PATTERN MISMATCH', [
                'calculated_hash' => $calculatedHash,
                'pattern' => $pattern,
                'hash_start' => substr($calculatedHash, 0, 10)
            ]);
            return ['valid' => false, 'error' => 'The string did not match the expected pattern.'];
        }

        if (ProofOfWork::where('hash', $calculatedHash)->exists()) {
            return ['valid' => false, 'error' => 'Duplicate proof'];
        }

        Log::info('PROOF VERIFIED SUCCESSFULLY', ['hash' => $calculatedHash]);
        return ['valid' => true];
    }

    private function calculatePoints($pattern)
    {
        $points = [
            '21' => 0.1, // Idle pattern - very low points
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 125,
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
        return response()->json([
            'total_proofs' => ProofOfWork::count(),
            'top_miners' => []
        ]);
    }

}
