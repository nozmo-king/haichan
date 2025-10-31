<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal21e8Achievement extends Model
{
    protected $table = 'personal_21e8_achievements';
    
    protected $fillable = [
        'user_id',
        'level',
        'hash',
        'nonce',
        'total_hashes',
        'mining_time',
        'points_awarded',
        'found_at',
        'ip_address',
    ];

    protected $casts = [
        'found_at' => 'datetime',
        'mining_time' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }

    public static function getLevels()
    {
        return [
            '21e8' => ['zeros' => 4, 'points' => 100, 'name' => '21e8'],
            '21e80' => ['zeros' => 5, 'points' => 500, 'name' => '21e80'],
            '21e800' => ['zeros' => 6, 'points' => 2500, 'name' => '21e800'],
            '21e8000' => ['zeros' => 7, 'points' => 10000, 'name' => '21e8000'],
            '21e80000' => ['zeros' => 8, 'points' => 50000, 'name' => '21e80000'],
            '21e800000' => ['zeros' => 9, 'points' => 250000, 'name' => '21e800000'],
            '21e8000000' => ['zeros' => 10, 'points' => 1250000, 'name' => '21e8000000'],
            '21e80000000' => ['zeros' => 11, 'points' => 6250000, 'name' => '21e80000000'],
            '21e800000000' => ['zeros' => 12, 'points' => 31250000, 'name' => '21e800000000'],
        ];
    }

    public static function getNextLevel($currentLevel)
    {
        $levels = array_keys(self::getLevels());
        $currentIndex = array_search($currentLevel, $levels);
        
        if ($currentIndex === false || $currentIndex >= count($levels) - 1) {
            return null;
        }
        
        return $levels[$currentIndex + 1];
    }
}
