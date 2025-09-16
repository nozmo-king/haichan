<?php

namespace App\Http\Controllers;

use App\Models\ProofSubmission;
use App\Models\Board;
use Illuminate\Http\Request;

class MiningController extends Controller
{
    public function dashboard(Request $request)
    {
        // Get current user session for personalized stats
        $userSession = ProofSubmission::generateUserSession($request->ip());

        // Get user stats (last 24 hours)
        $userStats = ProofSubmission::getUserStats($userSession, 24);

        // Get global network stats
        $globalStats = ProofSubmission::getGlobalStats(24);

        // Get board-specific stats for all boards
        $boards = Board::all();
        $boardStats = [];

        foreach ($boards as $board) {
            $stats = ProofSubmission::getBoardStats($board->code);
            if ($stats) {
                $boardStats[$board->code] = [
                    'name' => $board->name,
                    'icon' => $this->getBoardIcon($board->code),
                    'stats' => $stats
                ];
            }
        }

        // Get recent high-difficulty proofs for leaderboard
        $recentProofs = ProofSubmission::where('created_at', '>=', now()->subHours(24))
            ->where('difficulty', '>=', 5.0)
            ->orderByDesc('difficulty')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('mining.dashboard', compact('userStats', 'globalStats', 'boardStats', 'recentProofs'));
    }

    public function stats(Request $request)
    {
        $userSession = ProofSubmission::generateUserSession($request->ip());

        $userStats = ProofSubmission::getUserStats($userSession, 24);
        $globalStats = ProofSubmission::getGlobalStats(24);

        // Get board stats
        $boards = Board::all();
        $boardStats = [];

        foreach ($boards as $board) {
            $stats = ProofSubmission::getBoardStats($board->code);
            if ($stats) {
                $boardStats[$board->code] = [
                    'name' => $board->name,
                    'icon' => $this->getBoardIcon($board->code),
                    'stats' => $stats
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
