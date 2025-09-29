<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FriendCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'is_used',
        'used_by_user_id',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public static function generateCode(): string
    {
        return Str::random(32);
    }

    public function isValid(): bool
    {
        return ! $this->is_used &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
