<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'duration_months',
        'price_usd',
        'stripe_price_id',
        'stripe_product_id',
        'features',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_usd' => 'decimal:2'
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function needsStripeSetup(): bool
    {
        return empty($this->stripe_product_id) || empty($this->stripe_price_id);
    }

    public function isReadyForPayments(): bool
    {
        return !$this->needsStripeSetup();
    }
}
