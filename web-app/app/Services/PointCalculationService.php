<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PointCalculationService
{
    private $pointMap;

    public function __construct()
    {
        $this->pointMap = [
            // Standard patterns
            '2' => 1,
            '21' => 2.5,
            '21e' => 5,
            '21e8' => 10,
            '21e80' => 50,
            '21e800' => 250,
            '21e8000' => 1000,
            '000021e8' => 5000,

            // Personal 21e8 achievements
            '21e8' => 100,
            '21e80' => 500,
            '21e800' => 2500,
            '21e8000' => 10000,
            '21e80000' => 50000,

            // Legendary patterns
            '000' => 500,
            '111' => 400,
            '222' => 300,
            '333' => 350,
            '444' => 300,
            '555' => 450,
            '666' => 666,
            '777' => 777,
            '888' => 400,
            '999' => 350,

            // Hex letter patterns
            'aaa' => 250,
            'bbb' => 250,
            'ccc' => 250,
            'ddd' => 250,
            'eee' => 250,
            'fff' => 300,

            // 3-letter vanity words
            'ace' => 150,
            'bad' => 100,
            'cab' => 80,
            'dad' => 120,
            'ded' => 200,
            'fab' => 100,
            'fed' => 90,

            // 4-letter vanity words
            'beef' => 300,
            'cafe' => 250,
            'face' => 200,
            'babe' => 180,
            'fade' => 150,
            'dead' => 400,
            'deed' => 250,
            'feed' => 200,

            // Internet culture
            'deadbeef' => 3133,
            'c0de' => 1337,
            'b00b' => 800,
            '1337' => 1337,
            'pwnd' => 500,
            'rekt' => 400,
            'epic' => 300,
            'chad' => 250,
        ];
    }

    public function calculatePoints(string $pattern, ?string $hash = null): float
    {
        $basePoints = $this->pointMap[$pattern] ?? 0.1;

        if ($hash) {
            // Check for legendary trailing zeros in the actual hash (5+ zeros = legendary)
            if (preg_match('/0{5,}$/', $hash)) {
                preg_match('/0+$/', $hash, $matches);
                $trailingZeros = strlen($matches[0] ?? '');
                if ($trailingZeros >= 5) {
                    $legendaryBonus = pow(10, $trailingZeros - 4); // Exponential bonus starting at 5 zeros
                    $basePoints += $legendaryBonus * 1000; // Legendary status: minimum 1000 bonus points

                    Log::info('LEGENDARY HASH DETECTED!', [
                        'hash' => $hash,
                        'trailing_zeros' => $trailingZeros,
                        'base_points' => $basePoints - ($legendaryBonus * 1000),
                        'legendary_bonus' => $legendaryBonus * 1000,
                        'total_points' => $basePoints
                    ]);
                }
            }
        }

        return $basePoints;
    }
}
