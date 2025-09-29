<?php

namespace App\View\Composers;

use App\Models\ProofOfWork;
use Illuminate\View\View;

class GlobalStatsComposer
{
    public function compose(View $view): void
    {
        // Calculate real-time PoW statistics
        $totalProofs = ProofOfWork::count();
        $totalHashes = $totalProofs * 500000; // Estimate based on difficulty

        // Daily PoW (last 24 hours)
        $dailyProofs = ProofOfWork::where('verified_at', '>', now()->subDay())->count();

        // Weekly PoW (last 7 days)
        $weeklyProofs = ProofOfWork::where('verified_at', '>', now()->subWeek())->count();

        // Active miners (based on recent proof submissions)
        $recentProofs = ProofOfWork::where('verified_at', '>', now()->subMinutes(5))->count();
        $activeSessions = max(1, floor($recentProofs / 3));

        // Global hashrate estimation
        $globalHashrate = $recentProofs * 100000;

        $view->with([
            'totalProofs' => $totalProofs,
            'totalHashes' => $totalHashes,
            'dailyProofs' => $dailyProofs,
            'weeklyProofs' => $weeklyProofs,
            'activeSessions' => $activeSessions,
            'globalHashrate' => $globalHashrate,
        ]);
    }
}
