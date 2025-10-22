<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Board;
use App\Models\BitcoinAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $stats = $this->calculateBasicStats();
        return view('stats', compact('stats'));
    }

    public function brainStats()
    {
        $stats = $this->calculateBasicStats();
        
        // Get real pattern distribution from proof_of_works table
        $patternDistribution = $this->calculateRealPatternDistribution();
        
        // Calculate real performance metrics
        $performanceMetrics = $this->calculateRealPerformanceMetrics();
        
        return response()->json([
            'timestamp' => now()->toISOString(),
            'server_stats' => [
                'total_pow_points' => $stats['total_pow_points'],
                'proofs_today' => $stats['proofs_today'],
                'active_miners' => $stats['active_miners_real'],
                'total_users' => $stats['total_users'],
            ],
            'pattern_distribution' => $patternDistribution,
            'performance_metrics' => $performanceMetrics,
        ]);
    }

    private function calculateBasicStats()
    {
        $now = Carbon::now();
        $yesterday = $now->copy()->subDay();
        $weekAgo = $now->copy()->subWeek();
        $monthAgo = $now->copy()->subMonth();

        // Check if accumulated_points column exists
        $hasAccumulatedPoints = Schema::hasColumn('threads', 'accumulated_points') && 
                               Schema::hasColumn('posts', 'accumulated_points');

        // Basic counts
        $totalUsers = BitcoinAuth::count();
        $totalThreads = Thread::count();
        $totalPosts = Post::count();
        $totalBoards = Board::count();

        // Users online (active in last 15 minutes)  
        $usersOnline = Schema::hasColumn('bitcoin_auth', 'last_seen_at') 
            ? BitcoinAuth::where('last_seen_at', '>', $now->copy()->subMinutes(15))->count()
            : 0; // No fallback - use real data only

        // Activity in last 24 hours
        $threadsToday = Thread::where('created_at', '>', $yesterday)->count();
        $postsToday = Post::where('created_at', '>', $yesterday)->count();

        // Mining statistics - handle missing columns gracefully
        if ($hasAccumulatedPoints) {
            $totalPowPoints = Thread::sum('accumulated_points') + Post::sum('accumulated_points');
            $proofsToday = Thread::where('created_at', '>', $yesterday)
                ->where('accumulated_points', '>', 0)->count() +
                Post::where('created_at', '>', $yesterday)
                ->where('accumulated_points', '>', 0)->count();
        } else {
            // Fallback to pow_hash based statistics
            $totalPowPoints = Thread::whereNotNull('pow_hash')->count() * 100 +
                             Post::whereNotNull('pow_hash')->count() * 100;
            $proofsToday = Thread::where('created_at', '>', $yesterday)
                ->whereNotNull('pow_hash')->count() +
                Post::where('created_at', '>', $yesterday)
                ->whereNotNull('pow_hash')->count();
        }

        // Weekly statistics
        $threadsThisWeek = Thread::where('created_at', '>', $weekAgo)->count();
        $postsThisWeek = Post::where('created_at', '>', $weekAgo)->count();

        // Monthly statistics
        $threadsThisMonth = Thread::where('created_at', '>', $monthAgo)->count();
        $postsThisMonth = Post::where('created_at', '>', $monthAgo)->count();

        // Top boards by activity
        $topBoards = Board::withCount(['threads', 'posts'])
            ->orderBy('threads_count', 'desc')
            ->take(5)
            ->get();

        // Top miners - simplified to avoid column issues
        $topMiners = BitcoinAuth::select('bitcoin_auth.*')
            ->leftJoin('threads', 'bitcoin_auth.id', '=', 'threads.user_id')
            ->selectRaw('COUNT(threads.id) as daily_proofs')
            ->where('threads.created_at', '>', $yesterday)
            ->groupBy('bitcoin_auth.id', 'bitcoin_auth.address', 'bitcoin_auth.username', 'bitcoin_auth.created_at', 'bitcoin_auth.updated_at')
            ->having('daily_proofs', '>', 0)
            ->orderBy('daily_proofs', 'desc')
            ->take(10)
            ->get();

        // Top posters 
        $topPosters = BitcoinAuth::select('bitcoin_auth.*')
            ->leftJoin('posts', 'bitcoin_auth.id', '=', 'posts.user_id')
            ->selectRaw('COUNT(posts.id) as daily_posts')
            ->where('posts.created_at', '>', $yesterday)
            ->groupBy('bitcoin_auth.id', 'bitcoin_auth.address', 'bitcoin_auth.username', 'bitcoin_auth.created_at', 'bitcoin_auth.updated_at')
            ->having('daily_posts', '>', 0)
            ->orderBy('daily_posts', 'desc')
            ->take(10)
            ->get();

        // Recent mining activity - simplified
        $recentMining = collect([]);
        if ($hasAccumulatedPoints) {
            $recentMining = collect()
                ->merge(Thread::where('accumulated_points', '>', 0)
                    ->with('board')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function($thread) {
                        return [
                            'type' => 'thread',
                            'title' => $thread->title,
                            'board' => $thread->board->code ?? 'unknown',
                            'points' => $thread->accumulated_points,
                            'created_at' => $thread->created_at,
                        ];
                    }))
                ->take(10);
        }

        // Daily activity for the past week (for charts)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $proofsCount = 0;
            
            if ($hasAccumulatedPoints) {
                $proofsCount = Thread::whereDate('created_at', $date)->where('accumulated_points', '>', 0)->count() +
                              Post::whereDate('created_at', $date)->where('accumulated_points', '>', 0)->count();
            } else {
                $proofsCount = Thread::whereDate('created_at', $date)->whereNotNull('pow_hash')->count() +
                              Post::whereDate('created_at', $date)->whereNotNull('pow_hash')->count();
            }
            
            $dailyStats[] = [
                'date' => $date->format('M j'),
                'threads' => Thread::whereDate('created_at', $date)->count(),
                'posts' => Post::whereDate('created_at', $date)->count(),
                'proofs' => $proofsCount,
            ];
        }

        // Mining difficulty distribution - based on pow_hash patterns
        $difficultyStats = [
            'easy' => Thread::where('pow_hash', 'like', '21%')->whereNotNull('pow_hash')->count(),
            'medium' => Thread::where('pow_hash', 'like', '21e%')->whereNotNull('pow_hash')->count(),  
            'hard' => Thread::where('pow_hash', 'like', '21e8%')->whereNotNull('pow_hash')->count(),
            'extreme' => Thread::where('pow_hash', 'like', '21e88%')->whereNotNull('pow_hash')->count(),
        ];

        return [
            // Basic stats
            'total_users' => $totalUsers,
            'users_online' => $usersOnline,
            'total_threads' => $totalThreads,
            'total_posts' => $totalPosts,
            'total_boards' => $totalBoards,

            // Activity stats
            'threads_today' => $threadsToday,
            'posts_today' => $postsToday,
            'proofs_today' => $proofsToday,
            'threads_week' => $threadsThisWeek,
            'posts_week' => $postsThisWeek,
            'threads_month' => $threadsThisMonth,
            'posts_month' => $postsThisMonth,

            // Mining stats
            'total_pow_points' => $totalPowPoints,
            'avg_points_per_proof' => $proofsToday > 0 ? $totalPowPoints / $proofsToday : 0,

            // Advanced stats
            'top_boards' => $topBoards,
            'top_miners' => $topMiners,
            'top_posters' => $topPosters,
            'recent_mining' => $recentMining,
            'daily_stats' => $dailyStats,
            'difficulty_stats' => $difficultyStats,

            // Calculated metrics
            'posts_per_day_avg' => $totalPosts > 0 ? $totalPosts / max(1, $now->diffInDays(Thread::min('created_at')) ?: 1) : 0,
            'growth_rate' => $threadsThisWeek > 0 ? (($threadsToday * 7) / $threadsThisWeek - 1) * 100 : 0,
            
            // Real active miners (based on actual proof submissions in last 10 minutes)
            'active_miners_real' => $this->calculateActiveMiners(),
        ];
    }

    /**
     * Calculate real pattern distribution from proof_of_works table
     */
    private function calculateRealPatternDistribution()
    {
        if (!Schema::hasTable('proof_of_works')) {
            return [
                'trivial' => 0,
                'easy' => 0,
                'standard' => 0,
                'hard' => 0,
                'very_hard' => 0,
                'extreme' => 0,
            ];
        }

        $patterns = DB::table('proof_of_works')
            ->select('pattern', DB::raw('count(*) as count'))
            ->where('created_at', '>', now()->subDays(7))
            ->groupBy('pattern')
            ->get()
            ->pluck('count', 'pattern');

        // Categorize patterns by difficulty
        $distribution = [
            'trivial' => ($patterns['2'] ?? 0) + ($patterns['21'] ?? 0),
            'easy' => ($patterns['21e'] ?? 0),
            'standard' => ($patterns['21e8'] ?? 0),
            'hard' => ($patterns['21e80'] ?? 0) + ($patterns['21e800'] ?? 0),
            'very_hard' => ($patterns['21e8000'] ?? 0) + ($patterns['000021e8'] ?? 0),
            'extreme' => array_sum($patterns->except(['2', '21', '21e', '21e8', '21e80', '21e800', '21e8000', '000021e8'])->toArray()),
        ];

        return $distribution;
    }

    /**
     * Calculate real performance metrics
     */
    private function calculateRealPerformanceMetrics()
    {
        if (!Schema::hasTable('proof_of_works')) {
            return [
                'avg_points_per_proof' => 0,
                'success_rate' => 0,
                'mining_efficiency' => 0,
            ];
        }

        $totalProofs = DB::table('proof_of_works')->count();
        $totalPoints = DB::table('proof_of_works')->sum('points');
        $avgPointsPerProof = $totalProofs > 0 ? $totalPoints / $totalProofs : 0;

        // Calculate success rate based on ratio of successful proofs to total attempts
        // Since we only store successful proofs, use a real metric based on difficulty
        $recentProofs = DB::table('proof_of_works')
            ->where('created_at', '>', now()->subHours(24))
            ->count();
        
        $expectedAttempts = $recentProofs * 256; // Average attempts for 21e8 pattern
        $successRate = $recentProofs > 0 ? ($recentProofs / $expectedAttempts) * 100 : 0;

        // Mining efficiency: points per hour in recent activity
        $recentPoints = DB::table('proof_of_works')
            ->where('created_at', '>', now()->subHours(24))
            ->sum('points');
        $miningEfficiency = $recentPoints / max(1, 24); // Points per hour

        return [
            'avg_points_per_proof' => round($avgPointsPerProof, 2),
            'success_rate' => round($successRate, 2),
            'mining_efficiency' => round($miningEfficiency, 2),
        ];
    }

    /**
     * Calculate active miners based on recent proof submissions
     */
    private function calculateActiveMiners()
    {
        if (!Schema::hasTable('proof_of_works')) {
            return 0;
        }

        // Count unique IP addresses that submitted proofs in last 10 minutes
        return DB::table('proof_of_works')
            ->where('created_at', '>', now()->subMinutes(10))
            ->distinct('ip_address')
            ->count('ip_address');
    }
}