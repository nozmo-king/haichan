<?php

namespace App\Http\Controllers;

use App\Models\ProofOfWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Lean ProofOfWorkController for legacy verification support
 * Main PoW system now uses Api\PowController
 */
class ProofOfWorkController extends Controller
{
    /**
     * Verify proof for image mining and other legacy uses
     * Used by ImageLibraryController
     */
    public function verifyProof($data, $nonce, $submittedHash, $pattern)
    {
        Log::info('=== LEGACY PROOF VERIFICATION ===', [
            'data' => $data,
            'nonce' => $nonce,
            'submitted_hash' => $submittedHash,
            'pattern' => $pattern,
        ]);

        // SECURITY: Block dummy hash values
        if ($submittedHash === '21e8000000000000000000000000000000000000000000000000000000000000') {
            Log::warning('DUMMY HASH REJECTED', ['hash' => $submittedHash]);
            return ['valid' => false, 'error' => 'Dummy values not accepted'];
        }

        // Block suspiciously regular hashes (too many zeros)
        $zeroCount = substr_count($submittedHash, '0');
        if ($zeroCount > 50) {
            Log::warning('SUSPICIOUS HASH PATTERN REJECTED', [
                'hash' => $submittedHash,
                'zero_count' => $zeroCount,
            ]);
            return ['valid' => false, 'error' => 'Invalid hash pattern'];
        }

        // Verify the submitted hash matches the pattern
        if (!str_starts_with(strtolower($submittedHash), strtolower($pattern))) {
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
        $serverHash = hash('sha256', $data);

        Log::info('SERVER HASH VERIFICATION', [
            'data' => $data,
            'server_hash' => $serverHash,
            'client_hash' => $submittedHash,
            'hashes_match' => $serverHash === $submittedHash,
        ]);

        if ($serverHash !== $submittedHash) {
            Log::error('HASH VERIFICATION FAILED', [
                'server_sha256' => $serverHash,
                'client_submitted' => $submittedHash,
                'data' => $data,
            ]);
            return ['valid' => false, 'error' => 'Hash verification failed - server computed: ' . $serverHash];
        }

        Log::info('PROOF VERIFIED SUCCESSFULLY', [
            'hash' => $submittedHash,
            'verification_type' => 'SHA256',
        ]);

        return ['valid' => true];
    }

    /**
     * Get mining statistics
     */
    public function getStats()
    {
        $userId = session('bitcoin_auth_id');
        $userStats = [
            'total_points' => 0,
            'level' => 1,
            'session_proofs' => 0,
            'session_points' => 0,
        ];

        if ($userId) {
            $user = \App\Models\BitcoinAuth::find($userId);
            if ($user) {
                $sessionProofs = ProofOfWork::where('user_id', $userId)
                    ->where('created_at', '>', now()->subHour())
                    ->count();
                    
                $sessionPoints = ProofOfWork::where('user_id', $userId)
                    ->where('created_at', '>', now()->subHour())
                    ->sum('points');

                $userStats = [
                    'total_points' => $user->total_pow_points ?? 0,
                    'level' => $user->level ?? 1,
                    'session_proofs' => $sessionProofs,
                    'session_points' => $sessionPoints,
                ];
            }
        }

        // Get real network statistics
        $totalProofs = ProofOfWork::count();
        $recentProofs = ProofOfWork::where('created_at', '>', now()->subHours(24))->count();
        
        // Count unique IP addresses for active sessions estimate
        $activeSessions = ProofOfWork::where('created_at', '>', now()->subMinutes(10))
            ->distinct('ip_address')
            ->count('ip_address');

        return response()->json([
            'success' => true,
            'user' => $userStats,
            'total_proofs' => $totalProofs,
            'recent_proofs_24h' => $recentProofs,
            'active_miners' => max(1, $activeSessions),
            'total_points_awarded' => ProofOfWork::sum('points'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}