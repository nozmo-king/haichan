<?php

namespace App\Http\Controllers;

use App\Models\ProofOfWork;
use App\Models\Board;
use Illuminate\Http\Request;

class MiningController extends Controller
{
    public function dashboard(Request $request)
    {
        $userIp = $request->ip();

        // Get user stats (last 24 hours)
        $userProofs = ProofOfWork::where('ip_address', $userIp)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $userStats = [
            'total_proofs' => $userProofs->count(),
            'total_points' => $userProofs->sum('points'),
            'patterns' => $userProofs->groupBy('pattern')->map->count(),
        ];

        // Get global network stats
        $globalProofs = ProofOfWork::where('created_at', '>=', now()->subHours(24))->get();
        $globalStats = [
            'total_proofs' => $globalProofs->count(),
            'total_points' => $globalProofs->sum('points'),
            'unique_miners' => $globalProofs->pluck('ip_address')->unique()->count(),
            'patterns' => $globalProofs->groupBy('pattern')->map->count()->sortByDesc(function($count) {
                return $count;
            }),
        ];

        // Get board-specific stats for all boards
        $boards = Board::all();
        $boardStats = [];

        foreach ($boards as $board) {
            $boardProofs = ProofOfWork::whereHas('thread', function($query) use ($board) {
                $query->where('board_id', $board->id);
            })->where('created_at', '>=', now()->subHours(24))->get();

            if ($boardProofs->count() > 0) {
                $boardStats[$board->code] = [
                    'name' => $board->name,
                    'icon' => $this->getBoardIcon($board->code),
                    'stats' => [
                        'total_proofs' => $boardProofs->count(),
                        'total_points' => $boardProofs->sum('points'),
                        'patterns' => $boardProofs->groupBy('pattern')->map->count(),
                    ]
                ];
            }
        }

        // Get recent high-point proofs for leaderboard
        $recentProofs = ProofOfWork::where('created_at', '>=', now()->subHours(24))
            ->where('points', '>=', 25)
            ->orderByDesc('points')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('mining.dashboard', compact('userStats', 'globalStats', 'boardStats', 'recentProofs'));
    }

    public function stats(Request $request)
    {
        $userIp = $request->ip();

        // Get user stats (last 24 hours)
        $userProofs = ProofOfWork::where('ip_address', $userIp)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $userStats = [
            'total_proofs' => $userProofs->count(),
            'total_points' => $userProofs->sum('points'),
            'patterns' => $userProofs->groupBy('pattern')->map->count(),
        ];

        // Get global network stats
        $globalProofs = ProofOfWork::where('created_at', '>=', now()->subHours(24))->get();
        $globalStats = [
            'total_proofs' => $globalProofs->count(),
            'total_points' => $globalProofs->sum('points'),
            'unique_miners' => $globalProofs->pluck('ip_address')->unique()->count(),
            'patterns' => $globalProofs->groupBy('pattern')->map->count(),
        ];

        // Get board stats
        $boards = Board::all();
        $boardStats = [];

        foreach ($boards as $board) {
            $boardProofs = ProofOfWork::whereHas('thread', function($query) use ($board) {
                $query->where('board_id', $board->id);
            })->where('created_at', '>=', now()->subHours(24))->get();

            if ($boardProofs->count() > 0) {
                $boardStats[$board->code] = [
                    'name' => $board->name,
                    'icon' => $this->getBoardIcon($board->code),
                    'stats' => [
                        'total_proofs' => $boardProofs->count(),
                        'total_points' => $boardProofs->sum('points'),
                        'patterns' => $boardProofs->groupBy('pattern')->map->count(),
                    ]
                ];
            }
        }

        return response()->json([
            'user' => $userStats,
            'global' => $globalStats,
            'boards' => $boardStats
        ]);
    }

    private function getBoardIcon($boardCode)
    {
        $icons = [
            'gen' => '💬',
            'tech' => '💻',
            'biz' => '💼',
            'film' => '🎬',
            'x' => '👽',
            'lit' => '📚',
            'meta' => '⚙️',
            'mu' => '🎵'
        ];

        return $icons[$boardCode] ?? '📋';
    }
}
