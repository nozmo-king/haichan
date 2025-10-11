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
        
        // Calculate actual total hashes from difficulty patterns
        $totalHashes = 0;
        $powRecords = ProofOfWork::selectRaw('
            COUNT(*) as count,
            pattern
        ')->groupBy('pattern')->get();
        
        foreach ($powRecords as $record) {
            // Real hash estimates based on SHA-256 difficulty
            $hashesPerProof = match($record->pattern) {
                '21' => 256,          // ~2^8 hashes on average
                '21e' => 4096,        // ~2^12 hashes
                '21e8' => 65536,      // ~2^16 hashes
                '21e80' => 1048576,   // ~2^20 hashes
                '21e800' => 16777216, // ~2^24 hashes
                default => 1000
            };
            $totalHashes += $record->count * $hashesPerProof;
        }

        // Daily PoW (last 24 hours)
        $dailyProofs = ProofOfWork::where('verified_at', '>', now()->subDay())->count();

        // Weekly PoW (last 7 days)
        $weeklyProofs = ProofOfWork::where('verified_at', '>', now()->subWeek())->count();

        // Active miners (based on recent unique users)
        $activeSessions = ProofOfWork::where('verified_at', '>', now()->subMinutes(15))
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
            
        // Add anonymous miner estimate
        $anonProofs = ProofOfWork::where('verified_at', '>', now()->subMinutes(15))
            ->whereNull('user_id')
            ->count();
        $activeSessions += max(1, floor($anonProofs / 5)); // Conservative anon estimate

        // Real global hashrate (hashes per hour)
        $recentHashCount = ProofOfWork::where('verified_at', '>', now()->subHour())
            ->selectRaw('COUNT(*) as count, pattern')
            ->groupBy('pattern')
            ->get();
            
        $hourlyHashes = 0;
        foreach ($recentHashCount as $recent) {
            $hashesPerProof = match($recent->pattern) {
                '21' => 256,
                '21e' => 4096,
                '21e8' => 65536,
                '21e80' => 1048576,
                '21e800' => 16777216,
                default => 1000
            };
            $hourlyHashes += $recent->count * $hashesPerProof;
        }
        $globalHashrate = $hourlyHashes;

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
