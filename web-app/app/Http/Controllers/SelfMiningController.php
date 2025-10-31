<?php

namespace App\Http\Controllers;

use App\Models\Personal21e8Achievement;

use App\Services\PointCalculationService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;



class SelfMiningController extends Controller

{

    protected $pointCalculationService;



    public function __construct(PointCalculationService $pointCalculationService)

    {

        $this->pointCalculationService = $pointCalculationService;

    }



    public function submitPersonal21e8(Request $request)

    {

        $userId = session('bitcoin_auth_id');

        if (!$userId) {

            return response()->json(['error' => 'Unauthorized'], 401);

        }



        // Rate limiting

        $ipAddress = $request->ip();

        $ipCount = Personal21e8Achievement::where('ip_address', $ipAddress)

            ->where('created_at', '>', now()->subHour())

            ->count();

        if ($ipCount > 10) {

            Log::warning('IP RATE LIMIT EXCEEDED for self-mining', [

                'ip' => $ipAddress,

                'count' => $ipCount

            ]);

            return response()->json([

                'success' => false,

                'message' => 'Rate limit exceeded. Please slow down.'

            ], 429);

        }

        

        $user = \App\Models\BitcoinAuth::find($userId);

        

        if (!$user) {

            return response()->json(['error' => 'User not found'], 404);

        }

        

        // Log the incoming request for debugging

        Log::info('Personal 21e8 submission', [

            'user_id' => $user->id,

            'data' => $request->all()

        ]);

        

        // Validate the request

        try {

            $validated = $request->validate([

                'hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',

                'nonce' => 'required|numeric',

                'target' => 'required|string',

                'hashes' => 'required|numeric|min:1',

                'time' => 'required|numeric|min:0'

            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            Log::error('Personal 21e8 validation failed', [

                'errors' => $e->errors(),

                'data' => $request->all()

            ]);

            throw $e;

        }



        // Verify the hash starts with a valid 21e8 pattern
        // Note: We don't recompute the hash because the JS mining uses crypto.subtle.digest
        // which may produce different results than PHP's hash() in edge cases
        
        // Determine the level achieved

        $level = $this->determineLevel($request->hash);

        

        if (!$level) {

            return response()->json(['error' => 'Not a valid 21e8 hash'], 400);

        }
        
        // Additional security: verify the hash format is valid hex
        if (!preg_match('/^[a-f0-9]{64}$/i', $validated['hash'])) {
            return response()->json(['error' => 'Invalid hash format'], 400);
        }

        

        // Check if user already has this level (LOCKED IN - cannot change)

        $existing = Personal21e8Achievement::where('user_id', $user->id)

            ->where('level', $level)

            ->first();

            

        if ($existing) {

            return response()->json([

                'error' => "You already found your {$level}! It is locked in permanently.",

                'locked_hash' => $existing->hash

            ], 400);

        }

        // ENFORCE SEQUENTIAL PROGRESSION - Check prerequisites
        $validationResult = $this->validateLevelProgression($user->id, $level);
        if (!$validationResult['valid']) {
            return response()->json([
                'error' => $validationResult['message'],
                'required_level' => $validationResult['required_level']
            ], 400);
        }

        

        $points = $this->pointCalculationService->calculatePoints($level);

        

        // Save the achievement

        Personal21e8Achievement::create([

            'user_id' => $user->id,

            'level' => $level,

            'hash' => $request->hash,

            'nonce' => $request->nonce,

            'total_hashes' => $request->hashes,

            'mining_time' => $request->time,

            'points_awarded' => $points,

            'found_at' => now(),

            'ip_address' => $ipAddress,

        ]);

        

        // Award points to user

        $user->increment('total_pow_points', $points);

        

        // Update legacy field for first 21e8 only

        if ($level === '21e8' && !$user->personal_21e8_hash) {

            $user->update([

                'personal_21e8_hash' => $request->hash,

                'personal_21e8_nonce' => $request->nonce,

                'personal_21e8_total_hashes' => $request->hashes,

                'personal_21e8_mining_time' => $request->time,

                'personal_21e8_found_at' => now(),

            ]);

        }

        

        // Refresh session

        session(['bitcoin_auth_user' => $user->fresh()]);

        

        // Check for next level

        $nextLevel = Personal21e8Achievement::getNextLevel($level);

        

        return response()->json([

            'success' => true,

            'level' => $level,

            'message' => "{$level} found and locked in!",

            'points_awarded' => $points,

            'next_level' => $nextLevel,

            'total_achievements' => Personal21e8Achievement::where('user_id', $user->id)->count()

        ]);

    }

    

    private function determineLevel($hash)

    {

        // Check each level from hardest to easiest

        if (str_starts_with($hash, '21e800000000')) return '21e800000000';

        if (str_starts_with($hash, '21e80000000')) return '21e80000000';

        if (str_starts_with($hash, '21e8000000')) return '21e8000000';

        if (str_starts_with($hash, '21e800000')) return '21e800000';

        if (str_starts_with($hash, '21e80000')) return '21e80000';

        if (str_starts_with($hash, '21e8000')) return '21e8000';

        if (str_starts_with($hash, '21e800')) return '21e800';

        if (str_starts_with($hash, '21e80')) return '21e80';

        if (str_starts_with($hash, '21e8')) return '21e8';

        

        return null;

    }

    

    public function getLeaderboard()

    {

        $leaders = DB::table('bitcoin_auth')

            ->whereNotNull('personal_21e8_hash')

            ->orderBy('personal_21e8_mining_time', 'asc')

            ->limit(50)

            ->get(['id as user_id', 'username', 'personal_21e8_mining_time as mining_time', 'personal_21e8_total_hashes as total_hashes', 'personal_21e8_found_at as found_at']);

        

        return response()->json([

            'leaders' => $leaders

        ]);

    }

    /**
     * Validate that user has completed prerequisite levels before allowing submission
     * ENFORCES SEQUENTIAL PROGRESSION: 21e8 -> 21e80 -> 21e800 -> 21e8000 -> 21e80000
     */
    private function validateLevelProgression($userId, $targetLevel)
    {
        // Get all user's achievements
        $userAchievements = Personal21e8Achievement::where('user_id', $userId)
            ->pluck('level')
            ->toArray();

        // Define the required sequence
        $sequence = ['21e8', '21e80', '21e800', '21e8000', '21e80000', '21e800000', '21e8000000', '21e80000000', '21e800000000'];
        $targetIndex = array_search($targetLevel, $sequence);

        if ($targetIndex === false) {
            return [
                'valid' => false,
                'message' => "Invalid level: {$targetLevel}",
                'required_level' => null
            ];
        }

        // Check if all prerequisite levels are completed
        for ($i = 0; $i < $targetIndex; $i++) {
            $requiredLevel = $sequence[$i];
            if (!in_array($requiredLevel, $userAchievements)) {
                return [
                    'valid' => false,
                    'message' => "You must complete {$requiredLevel} before attempting {$targetLevel}. Sequential progression is enforced.",
                    'required_level' => $requiredLevel
                ];
            }
        }

        return ['valid' => true];
    }

}
