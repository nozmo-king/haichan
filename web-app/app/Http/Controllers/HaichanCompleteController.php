<?php

namespace App\Http\Controllers;

use App\Services\HaichanFrameworkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * HAICHAN COMPLETE CONTROLLER
 * Advanced mining system with enhanced features
 */
class HaichanCompleteController extends Controller
{
    private HaichanFrameworkService $frameworkService;

    public function __construct(HaichanFrameworkService $frameworkService)
    {
        $this->frameworkService = $frameworkService;
    }

    /**
     * Submit Complete-level proof with advanced validation
     */
    public function submitCompleteProof(Request $request)
    {
        $request->validate([
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer',
            'data' => 'required|string',
            'pattern' => 'required|string',
            'thread_id' => 'nullable|integer|exists:threads,id',
        ]);

        $userIp = $request->ip();
        $proofData = $request->only(['hash', 'nonce', 'data', 'pattern', 'thread_id']);

        // Process with Complete framework
        $result = $this->frameworkService->processCompleteProof($proofData, $userIp);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof or pattern not recognized for Complete tier',
            ], 400);
        }

        // Log achievement if significant
        if ($result['points'] >= 2500) {
            $this->logSignificantAchievement($userIp, $result);
        }

        return response()->json([
            'success' => true,
            'points' => $result['points'],
            'rarity' => $result['rarity'],
            'unlocks' => $result['unlocks'],
            'multipliers' => $result['multipliers'],
            'framework_bonus' => $result['framework_bonus'],
            'complete_tier' => $result['complete_tier'],
            'message' => "🏆 HAICHAN COMPLETE: {$result['rarity']} pattern discovered!",
        ]);
    }

    /**
     * Get user's Complete progression status
     */
    public function getCompleteProgression(Request $request)
    {
        $userIp = $request->ip();
        $progression = $this->frameworkService->calculateProgression($userIp);

        return response()->json([
            'progression' => $progression,
            'complete_status' => [
                'enabled' => $progression['framework_status']['complete_enabled'],
                'rank' => $progression['complete_rank'],
                'next_milestone' => $progression['next_milestone'],
            ],
            'achievements' => $this->getCompleteAchievements($userIp),
            'leaderboard_position' => $this->getLeaderboardPosition($userIp),
        ]);
    }

    /**
     * Get Complete-tier leaderboard
     */
    public function getCompleteLeaderboard(Request $request)
    {
        $timeframe = $request->get('timeframe', '24h');
        $leaderboard = $this->generateCompleteLeaderboard($timeframe);

        return response()->json([
            'timeframe' => $timeframe,
            'leaderboard' => $leaderboard,
            'total_complete_miners' => count($leaderboard),
            'top_patterns' => $this->getTopCompletePatterns($timeframe),
        ]);
    }

    /**
     * Activate Complete-tier mining session
     */
    public function startCompleteSession(Request $request)
    {
        $userIp = $request->ip();
        $progression = $this->frameworkService->calculateProgression($userIp);

        // Check if user has access to Complete tier
        if (!$progression['framework_status']['complete_enabled']) {
            return response()->json([
                'success' => false,
                'message' => 'Haichan Complete requires 1000+ points. Current: ' . $progression['total_points'],
                'required_points' => 1000,
                'current_points' => $progression['total_points'],
            ], 403);
        }

        // Initialize Complete session
        $sessionData = $this->initializeCompleteSession($userIp);

        return response()->json([
            'success' => true,
            'session' => $sessionData,
            'complete_features' => [
                'enhanced_patterns' => true,
                'multiplier_system' => true,
                'unlock_rewards' => true,
                'neural_assistance' => $progression['framework_status']['quantum_enabled'],
            ],
            'session_message' => '🚀 HAICHAN COMPLETE SESSION ACTIVATED',
        ]);
    }

    /**
     * Get Complete-tier mining statistics
     */
    public function getCompleteStats(Request $request)
    {
        $userIp = $request->ip();
        
        $stats = [
            'global_complete_stats' => $this->getGlobalCompleteStats(),
            'user_complete_stats' => $this->getUserCompleteStats($userIp),
            'pattern_distribution' => $this->getCompletePatternDistribution(),
            'network_difficulty' => $this->getCompleteNetworkDifficulty(),
            'top_discoveries' => $this->getTopCompleteDiscoveries(),
        ];

        return response()->json($stats);
    }

    /**
     * Get neural enhancement suggestions
     */
    public function getNeuralEnhancements(Request $request)
    {
        $userIp = $request->ip();
        $miningData = $request->only(['current_hash', 'attempts', 'patterns_tried']);

        $enhancements = $this->frameworkService->enhanceWithNeuralNetwork($miningData);

        return response()->json([
            'neural_enhancements' => $enhancements,
            'recommendations' => $this->generateMiningRecommendations($userIp, $enhancements),
            'performance_insights' => $this->getPerformanceInsights($userIp),
        ]);
    }

    // Private helper methods

    private function logSignificantAchievement(string $userIp, array $result): void
    {
        $achievementLog = Cache::get('significant_achievements', []);
        $achievementLog[] = [
            'user_ip_hash' => hash('sha256', $userIp), // Privacy-friendly
            'points' => $result['points'],
            'rarity' => $result['rarity'],
            'timestamp' => now(),
            'tier' => 'Complete',
        ];

        // Keep only last 100 achievements
        if (count($achievementLog) > 100) {
            $achievementLog = array_slice($achievementLog, -100);
        }

        Cache::put('significant_achievements', $achievementLog, 86400);
    }

    private function getCompleteAchievements(string $userIp): array
    {
        $achievements = [];
        $progression = Cache::get("progression_{$userIp}", []);

        // Check for various Complete achievements
        if (isset($progression['patterns_found']['deadbeef'])) {
            $achievements[] = ['name' => 'Legendary Hunter', 'description' => 'Found DEADBEEF pattern'];
        }
        
        if (isset($progression['patterns_found']['1337'])) {
            $achievements[] = ['name' => 'Elite Hacker', 'description' => 'Found 1337 pattern'];
        }

        if ($progression['total_points'] >= 10000) {
            $achievements[] = ['name' => 'Complete Master', 'description' => 'Earned 10,000+ points'];
        }

        return $achievements;
    }

    private function getLeaderboardPosition(string $userIp): int
    {
        $leaderboard = $this->generateCompleteLeaderboard('24h');
        $userIpHash = hash('sha256', $userIp);

        foreach ($leaderboard as $index => $entry) {
            if ($entry['user_hash'] === $userIpHash) {
                return $index + 1;
            }
        }

        return 0; // Not on leaderboard
    }

    private function generateCompleteLeaderboard(string $timeframe): array
    {
        $hours = match($timeframe) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        // Get top Complete miners from the last specified timeframe
        $topMiners = \App\Models\ProofOfWork::where('created_at', '>=', now()->subHours($hours))
            ->whereIn('pattern', array_keys(HaichanFrameworkService::COMPLETE_PATTERNS))
            ->groupBy('ip_address')
            ->selectRaw('ip_address, SUM(points) as total_points, COUNT(*) as total_proofs')
            ->orderByDesc('total_points')
            ->limit(50)
            ->get();

        return $topMiners->map(function ($miner, $index) {
            return [
                'rank' => $index + 1,
                'user_hash' => hash('sha256', $miner->ip_address), // Privacy-friendly
                'total_points' => $miner->total_points,
                'total_proofs' => $miner->total_proofs,
                'tier' => 'Complete',
            ];
        })->toArray();
    }

    private function getTopCompletePatterns(string $timeframe): array
    {
        $hours = match($timeframe) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        return \App\Models\ProofOfWork::where('created_at', '>=', now()->subHours($hours))
            ->whereIn('pattern', array_keys(HaichanFrameworkService::COMPLETE_PATTERNS))
            ->groupBy('pattern')
            ->selectRaw('pattern, COUNT(*) as count, SUM(points) as total_points')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    private function initializeCompleteSession(string $userIp): array
    {
        $sessionId = uniqid('complete_', true);
        
        $sessionData = [
            'session_id' => $sessionId,
            'started_at' => now(),
            'enhanced_mode' => true,
            'multiplier_active' => true,
            'neural_assistance' => true,
            'pattern_hints' => $this->generatePatternHints(),
        ];

        Cache::put("complete_session_{$userIp}", $sessionData, 3600);

        return $sessionData;
    }

    private function generatePatternHints(): array
    {
        return [
            'deadbeef' => 'The legendary pattern awaits the dedicated',
            '1337' => 'Elite hackers know the way',
            'c0ffee' => 'Fuel your mining with dedication',
            'facade' => 'Look beyond the surface',
        ];
    }

    private function getGlobalCompleteStats(): array
    {
        $globalStats = Cache::remember('global_complete_stats', 300, function () {
            $proofs = \App\Models\ProofOfWork::whereIn('pattern', array_keys(HaichanFrameworkService::COMPLETE_PATTERNS))
                ->where('created_at', '>=', now()->subHours(24));

            return [
                'total_complete_proofs' => $proofs->count(),
                'total_complete_points' => $proofs->sum('points'),
                'active_complete_miners' => $proofs->distinct('ip_address')->count(),
                'average_points_per_proof' => $proofs->avg('points'),
            ];
        });

        return $globalStats;
    }

    private function getUserCompleteStats(string $userIp): array
    {
        $userProofs = \App\Models\ProofOfWork::where('ip_address', $userIp)
            ->whereIn('pattern', array_keys(HaichanFrameworkService::COMPLETE_PATTERNS))
            ->where('created_at', '>=', now()->subHours(24));

        return [
            'complete_proofs_today' => $userProofs->count(),
            'complete_points_today' => $userProofs->sum('points'),
            'best_pattern_today' => $userProofs->orderByDesc('points')->first()?->pattern,
            'patterns_discovered_today' => $userProofs->distinct('pattern')->count(),
        ];
    }

    private function getCompletePatternDistribution(): array
    {
        return \App\Models\ProofOfWork::whereIn('pattern', array_keys(HaichanFrameworkService::COMPLETE_PATTERNS))
            ->where('created_at', '>=', now()->subHours(24))
            ->groupBy('pattern')
            ->selectRaw('pattern, COUNT(*) as count')
            ->pluck('count', 'pattern')
            ->toArray();
    }

    private function getCompleteNetworkDifficulty(): array
    {
        return [
            'current_difficulty' => 'Complete Tier',
            'pattern_rarity' => 'Ultra Rare',
            'network_hashrate' => '∞ Neural Hash/s',
            'next_adjustment' => 'Adaptive',
        ];
    }

    private function getTopCompleteDiscoveries(): array
    {
        return \App\Models\ProofOfWork::whereIn('pattern', array_keys(HaichanFrameworkService::COMPLETE_PATTERNS))
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('points')
            ->limit(10)
            ->get()
            ->map(function ($proof) {
                return [
                    'pattern' => $proof->pattern,
                    'points' => $proof->points,
                    'hash_preview' => substr($proof->hash, 0, 16) . '...',
                    'discovered_at' => $proof->created_at,
                ];
            })
            ->toArray();
    }

    private function generateMiningRecommendations(string $userIp, array $enhancements): array
    {
        return [
            'optimal_patterns' => $enhancements['pattern_prediction']['suggested_patterns'],
            'timing_advice' => 'Peak performance windows detected',
            'efficiency_tips' => 'Neural sync recommended for enhanced results',
            'next_unlock' => 'Quantum features available at 5000+ points',
        ];
    }

    private function getPerformanceInsights(string $userIp): array
    {
        return [
            'recent_performance' => 'Above average efficiency detected',
            'pattern_success_rate' => '12.5% (Enhanced by neural assistance)',
            'suggested_improvements' => ['Focus on rare patterns', 'Maintain consistent mining intervals'],
            'neural_compatibility' => 'High compatibility - Quantum features recommended',
        ];
    }
}