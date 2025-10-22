<?php

namespace App\Http\Controllers;

use App\Models\BitcoinAuth;
use App\Models\Challenge;
use App\Models\ProofOfWork;
use App\Services\ChallengeVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MiningController extends Controller
{
    protected $verifier;

    public function __construct(ChallengeVerifier $verifier)
    {
        $this->verifier = $verifier;
    }

    public function index()
    {
        return view('mining.index');
    }

    /**
     * Submit mining proof and award points properly
     */
    public function submitMiningProof(Request $request)
    {
        $request->validate([
            'challenge_token' => 'required|string',
            'client_nonce' => 'required|integer',
            'hash' => 'required|string|size:64',
        ]);

        try {
            // Verify the challenge
            $verificationResult = $this->verifier->verifyChallenge(
                $request->challenge_token,
                $request->client_nonce,
                $request->hash
            );

            if (!$verificationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $verificationResult['error'],
                ], 400);
            }

            $challenge = $verificationResult['challenge'];
            
            // Block dummy values
            $hash = $request->hash;
            if ($hash === '21e8000000000000000000000000000000000000000000000000000000000000') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dummy values not accepted',
                ], 400);
            }

            // Check for duplicate
            if (ProofOfWork::where('hash', $hash)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate proof',
                ], 400);
            }

            // Calculate points
            $points = $this->calculatePoints($challenge->difficulty, $hash);

            // Get authenticated user
            $userId = session('bitcoin_auth_id');
            $user = null;
            if ($userId) {
                $user = BitcoinAuth::find($userId);
            }

            // Create proof record
            $proofOfWork = ProofOfWork::create([
                'challenge_id' => $challenge->id,
                'user_id' => $user ? $user->id : null,
                'hash' => $hash,
                'nonce' => $request->client_nonce,
                'data' => json_encode($challenge->canonical_payload),
                'pattern' => $challenge->difficulty,
                'points' => $points,
                'verified_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            // Award points to user if logged in
            if ($user) {
                $user->awardMiningPoints($points);
                
                Log::info('Mining points awarded', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'points_awarded' => $points,
                    'total_points' => $user->fresh()->total_pow_points,
                    'hash' => $hash,
                    'pattern' => $challenge->difficulty
                ]);
            }

            // Mark challenge as used
            $challenge->markAsUsed();

            return response()->json([
                'success' => true,
                'message' => 'Mining proof accepted!',
                'points' => $points,
                'total_points' => $user ? $user->fresh()->total_pow_points : $points,
                'user_level' => $user ? $user->fresh()->level : 1,
                'hash' => $hash,
                'pattern' => $challenge->difficulty,
            ]);

        } catch (\Exception $e) {
            Log::error('Mining proof submission failed', [
                'error' => $e->getMessage(),
                'hash' => $request->hash ?? 'unknown',
                'user_id' => session('bitcoin_auth_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mining proof failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user mining stats
     */
    public function getStats()
    {
        $userId = session('bitcoin_auth_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }

        $user = BitcoinAuth::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Get recent proofs
        $recentProofs = ProofOfWork::where('user_id', $userId)
            ->where('created_at', '>', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['hash', 'points', 'pattern', 'created_at']);

        // Get session stats (last hour)
        $sessionProofs = ProofOfWork::where('user_id', $userId)
            ->where('created_at', '>', now()->subHour())
            ->count();

        $sessionPoints = ProofOfWork::where('user_id', $userId)
            ->where('created_at', '>', now()->subHour())
            ->sum('points');

        return response()->json([
            'success' => true,
            'user' => [
                'username' => $user->username,
                'total_points' => $user->total_pow_points,
                'level' => $user->level,
                'mining_power' => $user->mining_power,
            ],
            'session' => [
                'proofs' => $sessionProofs,
                'points' => $sessionPoints,
            ],
            'recent_proofs' => $recentProofs,
        ]);
    }

    /**
     * Calculate PoW points based on hash pattern difficulty
     */
    private function calculatePoints($expectedPattern, $hash)
    {
        $hash = strtolower($hash);
        $expectedPattern = strtolower($expectedPattern);

        // Base points for different patterns - UPDATED
        $pointMap = [
            '2' => 1,
            '21' => 2.5,
            '21e' => 5,
            '21e8' => 10,
            '21e80' => 50,
            '21e800' => 250,
            '000' => 500,
            '111' => 400,
            '666' => 666,
            '777' => 777,
            'deadbeef' => 3133,
            '1337' => 1337,
            'c0de' => 1000,
            'beef' => 300,
        ];

        // Check for exact pattern match first
        if (isset($pointMap[$expectedPattern])) {
            $basePoints = $pointMap[$expectedPattern];
        } else {
            $basePoints = 0.1; // Default minimum
        }

        // Bonus for exceeding expected difficulty
        if (str_starts_with($hash, '21e800') && $expectedPattern !== '21e800') {
            $basePoints *= 25;
        } elseif (str_starts_with($hash, '21e80') && !in_array($expectedPattern, ['21e80', '21e800'])) {
            $basePoints *= 5;
        } elseif (str_starts_with($hash, '21e8') && !in_array($expectedPattern, ['21e8', '21e80', '21e800'])) {
            $basePoints *= 2;
        }

        // Special rare patterns bonus
        if (str_starts_with($hash, '000')) {
            $basePoints *= 10;
        } elseif (str_starts_with($hash, '666')) {
            $basePoints *= 15;
        } elseif (str_contains($hash, 'dead')) {
            $basePoints *= 8;
        } elseif (str_contains($hash, '1337')) {
            $basePoints *= 5;
        } elseif (str_contains($hash, 'beef')) {
            $basePoints *= 3;
        }

        return max(0.1, $basePoints);
    }
}