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