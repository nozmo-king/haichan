<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    protected $fillable = [
        'code',
        'created_by',
        'used_by',
        'uses_remaining',
        'expires_at',
        'is_genesis',
        'mining_bonus'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_genesis' => 'boolean',
        'mining_bonus' => 'decimal:2'
    ];

    /**
     * Code creator relationship
     */
    public function creator()
    {
        return $this->belongsTo(BitcoinAuth::class, 'created_by');
    }

    /**
     * Code user relationship
     */
    public function user()
    {
        return $this->belongsTo(BitcoinAuth::class, 'used_by');
    }

    /**
     * Use this invite code
     */
    public function useCode($userId)
    {
        if ($this->uses_remaining <= 0) {
            throw new \Exception('Invite code exhausted');
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            throw new \Exception('Invite code expired');
        }

        $this->used_by = $userId;
        $this->uses_remaining--;
        $this->save();

        // Award creator bonus
        if ($this->creator) {
            $this->creator->awardMiningPoints(100); // Bonus for successful invite
        }

        return true;
    }

    /**
     * Create genesis invite codes (admin only)
     */
    public static function createGenesisCode($count = 1)
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = self::create([
                'code' => 'GENESIS' . strtoupper(bin2hex(random_bytes(4))),
                'created_by' => null,
                'uses_remaining' => 1,
                'is_genesis' => true,
                'mining_bonus' => 2.0, // 2x mining bonus for genesis users
                'expires_at' => now()->addDays(30)
            ]);
        }

        return $codes;
    }

    /**
     * Get remaining user slots
     */
    public static function getRemainingSlots()
    {
        $currentUsers = BitcoinAuth::count();
        return max(0, 256 - $currentUsers);
    }

    /**
     * Check if new registrations are allowed
     */
    public static function canRegister()
    {
        return self::getRemainingSlots() > 0;
    }

    /**
     * Generate exclusive early access codes
     */
    public static function generateEarlyAccess($count = 50)
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = self::create([
                'code' => 'EARLY' . strtoupper(bin2hex(random_bytes(5))),
                'created_by' => null,
                'uses_remaining' => 1,
                'is_genesis' => false,
                'mining_bonus' => 1.5, // 50% mining bonus for early users
                'expires_at' => now()->addDays(14)
            ]);
        }

        return $codes;
    }

    /**
     * Get invite status for login page
     */
    public static function getInviteStatus()
    {
        $currentUsers = BitcoinAuth::count();
        $remainingSlots = max(0, 256 - $currentUsers);
        
        return [
            'current_users' => $currentUsers,
            'remaining_slots' => $remainingSlots,
            'registration_open' => $remainingSlots > 0
        ];
    }
}