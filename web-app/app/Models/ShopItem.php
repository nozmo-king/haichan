<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'type',
        'metadata',
        'is_active',
        'stock',
        'level_required',
        'icon',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(ShopPurchase::class);
    }

    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->stock !== null && $this->stock <= 0) {
            return false;
        }

        return true;
    }

    public function canBePurchasedBy($user)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($user->level < $this->level_required) {
            return false;
        }

        if ($user->total_pow_points < $this->price) {
            return false;
        }

        return true;
    }
}
