<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Board extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'active', 'total_pow_points', 'pow_submissions_count', 'last_pow_at',
        'total_pow', 'daily_pow', 'weekly_pow', 'activity_score', 'display_order', 'shift_metadata', 'last_pow_update',
        'is_doodle_board', 'doodle_config'
    ];

    protected $casts = [
        'active' => 'boolean',
        'total_pow_points' => 'decimal:2',
        'pow_submissions_count' => 'integer',
        'last_pow_at' => 'datetime',
        'total_pow' => 'integer',
        'daily_pow' => 'integer',
        'weekly_pow' => 'integer',
        'activity_score' => 'decimal:2',
        'shift_metadata' => 'array',
        'last_pow_update' => 'datetime',
        'is_doodle_board' => 'boolean',
        'doodle_config' => 'array'
    ];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, Thread::class);
    }

    public function getUrlAttribute()
    {
        return "/{$this->code}";
    }

    public function getTitleAttribute()
    {
        $titles = [
            'gen' => '/gen/ - General',
            'tech' => '/tech/ - Technology', 
            'biz' => '/biz/ - Business',
            'film' => '/film/ - Film & TV',
            'x' => '/x/ - Paranormal',
            'lit' => '/lit/ - Literature',
            'meta' => '/meta/ - Meta',
            'mu' => '/mu/ - Music'
        ];
        
        return $titles[$this->code] ?? "/{$this->code}/";
    }

    public function getPostCountAttribute()
    {
        return $this->threads()->sum('reply_count') + $this->threads()->count();
    }

    public function getLastPostAtAttribute()
    {
        $lastThread = $this->threads()->latest('created_at')->first();
        return $lastThread ? $lastThread->created_at : $this->updated_at;
    }

    public function incrementPostCount()
    {
        $this->touch();
    }

    // Override the scope to handle missing 'active' column
    public function scopeActive($query)
    {
        if (Schema::hasColumn('boards', 'active')) {
            return $query->where('active', 1);
        }
        return $query; // Return all if no active column
    }

    // Static method to get active boards
    public static function getActiveBoards()
    {
        if (Schema::hasColumn('boards', 'active')) {
            return static::where('active', 1)->orderBy('total_pow_points', 'desc')->get();
        }
        return static::orderBy('total_pow_points', 'desc')->get();
    }

    // Add PoW points to this board
    public function addPowPoints(float $points)
    {
        $this->increment('total_pow_points', $points);
        $this->increment('pow_submissions_count');
        $this->update(['last_pow_at' => now()]);
    }

    // Get boards sorted by PoW activity
    public static function getByPowActivity($limit = 10)
    {
        return static::orderBy('total_pow_points', 'desc')
                    ->orderBy('last_pow_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // Get total PoW for all threads in this board
    public function getTotalThreadPowAttribute()
    {
        return $this->threads()->sum('pow_difficulty');
    }

    /**
     * Update board PoW stats and activity score
     */
    public function updatePowStats()
    {
        $now = now();

        // Calculate daily PoW (last 24 hours)
        $dailyPow = $this->threads()
            ->where('created_at', '>', $now->subDay())
            ->sum('pow_difficulty');

        // Calculate weekly PoW (last 7 days)
        $weeklyPow = $this->threads()
            ->where('created_at', '>', $now->subWeek())
            ->sum('pow_difficulty');

        // Calculate total PoW
        $totalPow = $this->threads()->sum('pow_difficulty');

        // Calculate activity score based on recent activity
        $recentThreads = $this->threads()->where('created_at', '>', $now->subHours(6))->count();
        $recentPosts = Post::whereIn('thread_id', $this->threads()->pluck('id'))
            ->where('created_at', '>', $now->subHours(6))
            ->count();

        $activityScore = ($dailyPow * 2) + ($recentThreads * 100) + ($recentPosts * 10);

        $this->update([
            'daily_pow' => $dailyPow,
            'weekly_pow' => $weeklyPow,
            'total_pow' => $totalPow,
            'activity_score' => $activityScore,
            'last_pow_update' => $now,
        ]);
    }

    /**
     * Get ever-shifting board order - changes based on activity and PoW
     */
    public static function getShiftingOrder($limit = null)
    {
        $query = static::selectRaw('*, (activity_score + total_pow / 100 + RANDOM() * 50) as shift_weight')
                      ->orderBy('shift_weight', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get boards for mouseover-reactive display
     */
    public static function getMovingBoards()
    {
        return static::selectRaw('*, (activity_score + daily_pow * 5) as power_level')
                    ->orderBy('power_level', 'desc')
                    ->get()
                    ->shuffle(); // Randomize initial positions
    }

    /**
     * Award PoW to this board when threads/posts are created
     */
    public function awardPoW(int $points)
    {
        $this->increment('total_pow', $points);
        $this->increment('daily_pow', $points);
        $this->increment('weekly_pow', $points);
        $this->increment('activity_score', $points * 2);
    }

    /**
     * Get board position in shifting order (for animations)
     */
    public function getShiftPosition(): int
    {
        $allBoards = static::getShiftingOrder();
        foreach ($allBoards as $index => $board) {
            if ($board->id === $this->id) {
                return $index + 1;
            }
        }
        return 0;
    }
}
