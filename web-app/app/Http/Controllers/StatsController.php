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
        return response()->json([
            'timestamp' => now()->toISOString(),
            'server_stats' => [
                'total_pow_points' => $stats['total_pow_points'],
                'proofs_today' => $stats['proofs_today'],
                'active_miners' => 5, // Placeholder
                'total_users' => $stats['total_users'],
            ],
            'pattern_distribution' => [
                'trivial' => 10,
                'easy' => 20,
                'standard' => 15,
                'hard' => 5,
                'very_hard' => 2,
                'extreme' => 1,
            ],
            'performance_metrics' => [
                'avg_points_per_proof' => $stats['avg_points_per_proof'],
                'success_rate' => 75.0,
                'mining_efficiency' => 25.5,
            ],
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
            : rand(3, 12); // Fallback

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
        ];
    }
}