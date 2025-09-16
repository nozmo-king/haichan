<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'hashes_computed',
        'valid_proofs', 'points_earned', 'started_at', 'last_activity',
        'ended_at', 'active'
    ];

    protected $casts = [
        'hashes_computed' => 'integer',
        'valid_proofs' => 'integer',
        'points_earned' => 'integer',
        'active' => 'boolean',
        'started_at' => 'datetime',
        'last_activity' => 'datetime', 
        'ended_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        $end = $this->ended_at ?? $this->last_activity ?? now();
        return $this->started_at->diffInSeconds($end);
    }

    public function getHashrateAttribute()
    {
        $duration = $this->duration;
        return $duration > 0 ? round($this->hashes_computed / $duration, 2) : 0;
    }

    public function updateActivity($hashesComputed = 0, $validProofs = 0, $pointsEarned = 0)
    {
        $this->update([
            'hashes_computed' => $this->hashes_computed + $hashesComputed,
            'valid_proofs' => $this->valid_proofs + $validProofs,
            'points_earned' => $this->points_earned + $pointsEarned,
            'last_activity' => now()
        ]);
    }

    public function endSession()
    {
        $this->update(['active' => false, 'ended_at' => now()]);
    }
}
