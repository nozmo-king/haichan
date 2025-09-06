<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Board extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'active', 'total_pow_points', 'pow_submissions_count', 'last_pow_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'total_pow_points' => 'decimal:2',
        'pow_submissions_count' => 'integer',
        'last_pow_at' => 'datetime'
    ];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function getUrlAttribute()
    {
        return "/{$this->name}";
    }

    public function getTitleAttribute()
    {
        $titles = [
            'gen' => '/gen/ - General',
            'tech' => '/tech/ - Technology', 
            'biz' => '/biz/ - Business',
            'film' => '/film/ - Film & TV',
            'x' => '/x/ - Paranormal',
            'lit' => '/lit/ - Literature'
        ];
        
        return $titles[$this->name] ?? "/{$this->name}/";
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
}
