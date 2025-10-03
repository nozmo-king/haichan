<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Challenge extends Model
{
    protected $table = 'pow_challenges';

    protected $fillable = [
        'token',
        'board_id',
        'target_type',
        'target_id',
        'action',
        'difficulty',
        'server_nonce',
        'canonical_payload',
        'hmac_signature',
        'issued_at',
        'expires_at',
        'used_at',
        'user_id',
        'ip_address',
    ];

    protected $casts = [
        'canonical_payload' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($challenge) {
            if (empty($challenge->token)) {
                $challenge->token = (string) Str::uuid();
            }
        });
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function user()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }

    public function proofs()
    {
        return $this->hasMany(ProofOfWork::class, 'challenge_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }
}
