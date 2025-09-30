<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Board;
use App\Models\BitcoinAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $stats = $this->calculateStats();
        return view('stats', compact('stats'));
    }

    private function calculateStats()
    {
        $now = Carbon::now();
        $yesterday = $now->copy()->subDay();
        $weekAgo = $now->copy()->subWeek();
        $monthAgo = $now->copy()->subMonth();

        // Basic counts
        $totalUsers = BitcoinAuth::count();
        $totalThreads = Thread::count();
        $totalPosts = Post::count();
        $totalBoards = Board::count();

        // Users online (active in last 15 minutes)
        $usersOnline = BitcoinAuth::where('last_seen_at', '>', $now->copy()->subMinutes(15))->count();

        // Activity in last 24 hours
        $threadsToday = Thread::where('created_at', '>', $yesterday)->count();
        $postsToday = Post::where('created_at', '>', $yesterday)->count();

        // Mining statistics
        $totalPowPoints = Thread::sum('accumulated_points') + Post::sum('accumulated_points');
        $proofsToday = Thread::where('created_at', '>', $yesterday)
            ->where('accumulated_points', '>', 0)->count() +
            Post::where('created_at', '>', $yesterday)
            ->where('accumulated_points', '>', 0)->count();

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

        // Top miners (users with most proofs today)
        $topMiners = BitcoinAuth::select('bitcoin_auth.*')
            ->selectRaw('(SELECT COUNT(*) FROM threads WHERE user_id = bitcoin_auth.id AND created_at > ? AND accumulated_points > 0) + 
                        (SELECT COUNT(*) FROM posts WHERE user_id = bitcoin_auth.id AND created_at > ? AND accumulated_points > 0) as daily_proofs', 
                        [$yesterday, $yesterday])
            ->having('daily_proofs', '>', 0)
            ->orderBy('daily_proofs', 'desc')
            ->take(10)
            ->get();

        // Top posters (users with most posts today)
        $topPosters = BitcoinAuth::select('bitcoin_auth.*')
            ->selectRaw('(SELECT COUNT(*) FROM threads WHERE user_id = bitcoin_auth.id AND created_at > ?) + 
                        (SELECT COUNT(*) FROM posts WHERE user_id = bitcoin_auth.id AND created_at > ?) as daily_posts', 
                        [$yesterday, $yesterday])
            ->having('daily_posts', '>', 0)
            ->orderBy('daily_posts', 'desc')
            ->take(10)
            ->get();

        // Recent mining activity
        $recentMining = collect()
            ->merge(Thread::where('accumulated_points', '>', 0)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($thread) {
                    return [
                        'type' => 'thread',
                        'title' => $thread->title,
                        'board' => $thread->board->code,
                        'points' => $thread->accumulated_points,
                        'created_at' => $thread->created_at,
                    ];
                }))
            ->merge(Post::where('accumulated_points', '>', 0)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($post) {
                    return [
                        'type' => 'post',
                        'title' => 'Reply in: ' . $post->thread->title,
                        'board' => $post->thread->board->code,
                        'points' => $post->accumulated_points,
                        'created_at' => $post->created_at,
                    ];
                }))
            ->sortByDesc('created_at')
            ->take(10);

        // Daily activity for the past week (for charts)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dailyStats[] = [
                'date' => $date->format('M j'),
                'threads' => Thread::whereDate('created_at', $date)->count(),
                'posts' => Post::whereDate('created_at', $date)->count(),
                'proofs' => Thread::whereDate('created_at', $date)->where('accumulated_points', '>', 0)->count() +
                          Post::whereDate('created_at', $date)->where('accumulated_points', '>', 0)->count(),
            ];
        }

        // Mining difficulty distribution
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