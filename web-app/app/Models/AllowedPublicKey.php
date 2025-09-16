<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllowedPublicKey extends Model
{
    protected $fillable = [
        'public_key',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function isAllowed(string $publicKey): bool
    {
        return self::active()->where('public_key', $publicKey)->exists();
    }
}
