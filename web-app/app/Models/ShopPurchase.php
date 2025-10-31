<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'shop_item_id',
        'price_paid',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }

    public function item()
    {
        return $this->belongsTo(ShopItem::class, 'shop_item_id');
    }

    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->is_active && !$this->isExpired();
    }
}
