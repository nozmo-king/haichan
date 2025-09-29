<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProofSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProofController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string|max:32',
            'target_id' => 'required|string|max:64',
            'pattern' => 'required|string|max:16',
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer',
            'challenge_data' => 'required|string',
            'hashes_computed' => 'nullable|integer|min:1',
            'metadata' => 'nullable|array',
        ]);

        // Verify the proof
        $fullData = $request->challenge_data.':'.$request->nonce;
        $calculatedHash = hash('sha256', $fullData);

        // Debug logging
        Log::info('Proof verification attempt', [
            'challenge_data' => $request->challenge_data,
            'nonce' => $request->nonce,
            'full_data' => $fullData,
            'submitted_hash' => $request->hash,
            'calculated_hash' => $calculatedHash,
            'hashes_match' => $calculatedHash === strtolower($request->hash),
        ]);

        if ($calculatedHash !== strtolower($request->hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Hash verification failed',
                'debug' => [
                    'expected' => $calculatedHash,
                    'received' => strtolower($request->hash),
                ],
            ], 400);
        }

        if (! str_starts_with(strtolower($calculatedHash), strtolower($request->pattern))) {
            return response()->json([
                'success' => false,
                'message' => 'Pattern verification failed',
            ], 400);
        }

        // Generate user session based on IP
        $userSession = ProofSubmission::generateUserSession($request->ip());

        // Calculate content hash if possible
        $contentHash = null;
        $challengeParts = explode(':', $request->challenge_data);
        if (count($challengeParts) >= 3) {
            $content = $challengeParts[2];
            $contentHash = $content ? hash('sha256', $content) : null;
        }

        // Record the proof
        try {
            ProofSubmission::recordProof(
                $userSession,
                $request->target_type,
                $request->target_id,
                $request->pattern,
                $request->hash,
                $request->nonce,
                $request->challenge_data,
                $contentHash,
                $request->ip(),
                $request->metadata ?: [],
                $request->hashes_computed
            );

            return response()->json([
                'success' => true,
                'message' => 'Proof accepted and recorded',
                'difficulty' => ProofSubmission::calculateDifficulty($request->pattern),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record proof: '.$e->getMessage(),
            ], 500);
        }
    }

    public function stats(Request $request)
    {
        $userSession = ProofSubmission::generateUserSession($request->ip());

        $userStats = ProofSubmission::getUserStats($userSession);
        $globalStats = ProofSubmission::getGlobalStats();

        return response()->json([
            'user' => $userStats,
            'global' => $globalStats,
        ]);
    }
}
