<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount_usd',
        'cryptocurrency',
        'crypto_amount',
        'wallet_address',
        'transaction_hash',
        'status',
        'payment_gateway',
        'gateway_payment_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'confirmed_at',
        'expires_at'
    ];

    protected $casts = [
        'amount_usd' => 'decimal:2',
        'crypto_amount' => 'decimal:8',
        'confirmed_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
