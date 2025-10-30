<?php

namespace App\Http\Controllers;

use App\Models\BitcoinAuth;
use App\Models\Challenge;
use App\Models\ProofOfWork;
use App\Services\ChallengeVerifier;
use App\Services\PointCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MiningController extends Controller
{
    protected $verifier;
    protected $pointCalculationService;

    public function __construct(ChallengeVerifier $verifier, PointCalculationService $pointCalculationService)
    {
        $this->verifier = $verifier;
        $this->pointCalculationService = $pointCalculationService;
    }


    /**
     * Submit mining proof and award points properly
     */
    public function submitMiningProof(Request $request)
    {
        // Rate limiting check
        $userId = session('bitcoin_auth_id');
        $ipAddress = $request->ip();
        
        if ($userId) {
            $recentCount = ProofOfWork::where('user_id', $userId)
                ->where('created_at', '>', now()->subMinute())
                ->count();
                
            if ($recentCount > 20) {
                Log::warning('RATE LIMIT EXCEEDED', [
                    'user_id' => $userId,
                    'count' => $recentCount
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please slow down.'
                ], 429);
            }
        } else {
            // IP-based rate limiting for anonymous users
            $ipCount = ProofOfWork::where('ip_address', $ipAddress)
                ->where('created_at', '>', now()->subMinute())
                ->count();
                
            if ($ipCount > 30) {
                Log::warning('IP RATE LIMIT EXCEEDED', [
                    'ip' => $ipAddress,
                    'count' => $ipCount
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please slow down.'
                ], 429);
            }
        }

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
            $zeroCount = substr_count($hash, '0');
            if ($zeroCount > 50) {
                Log::warning('SUSPICIOUS HASH PATTERN REJECTED', [
                    'hash' => $hash,
                    'zero_count' => $zeroCount,
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hash pattern',
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
            $points = $this->pointCalculationService->calculatePoints($challenge->difficulty, $hash);

            // Get authenticated user
            $user = $userId ? BitcoinAuth::find($userId) : null;

            DB::transaction(function () use ($challenge, $user, $hash, $request, $points) {
                // Create proof record
                ProofOfWork::create([
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
            });

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
     * Show mining dashboard
     */
    public function dashboard()
    {
        $userId = session('bitcoin_auth_id');
        $user = $userId ? BitcoinAuth::find($userId) : null;
        
        // Get boards
        $boards = \App\Models\Board::all();
        
        // Get mining stats
        $totalProofs = ProofOfWork::count();
        $totalMiners = ProofOfWork::distinct('user_id')->whereNotNull('user_id')->count('user_id');
        $recentProofs = ProofOfWork::orderBy('created_at', 'desc')->take(20)->get();
        
        // Active sessions (last 15 minutes)
        $activeSessions = ProofOfWork::where('created_at', '>', now()->subMinutes(15))
            ->distinct('user_id')
            ->whereNotNull('user_id')
            ->count('user_id');
        
        // Top miners (by points)
        $topMiners = BitcoinAuth::orderBy('total_pow_points', 'desc')
            ->take(10)
            ->get();
        
        // Recent threads (if needed)
        $recentThreads = \App\Models\Thread::orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('mining-market-dashboard', compact(
            'user', 'boards', 'totalProofs', 'totalMiners', 
            'recentProofs', 'activeSessions', 'topMiners', 'recentThreads'
        ));
    }

    /**
     * Get user mining stats
     */
    public function getStats()
    {
        $userId = session('bitcoin_auth_id');
        
        // Allow guest access with limited stats
        if (!$userId) {
            return response()->json([
                'success' => true,
                'guest' => true,
                'user' => [
                    'username' => 'Guest',
                    'total_points' => 0,
                    'level' => 1,
                    'mining_power' => 1,
                ],
                'session' => [
                    'proofs' => 0,
                    'points' => 0,
                    'hash_rate' => 0,
                ],
                'recent_proofs' => [],
                'target' => 'jcb:guest:mining',
            ]);
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
}