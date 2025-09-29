<?php

namespace App\Services;

use App\Models\FriendCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FriendCodeService
{
    public function generateFriendCode(User $user, int $expiryDays = 30): FriendCode
    {
        $existingCode = $user->friendCodes()->where('is_used', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($existingCode) {
            return $existingCode;
        }

        $code = $this->generateUniqueCode();
        $expiresAt = $expiryDays > 0 ? Carbon::now()->addDays($expiryDays) : null;

        return FriendCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
    }

    public function validateFriendCode(string $code): ?FriendCode
    {
        $friendCode = FriendCode::where('code', $code)->first();

        if (! $friendCode || ! $friendCode->isValid()) {
            return null;
        }

        return $friendCode;
    }

    public function useFriendCode(string $code, User $newUser): bool
    {
        $friendCode = $this->validateFriendCode($code);

        if (! $friendCode) {
            return false;
        }

        // Special case: infinite use friend codes starting with "georgebush"
        if (str_starts_with($code, 'georgebush')) {
            // Don't mark as used for infinite use codes
            // Just record the usage without marking as used
            return true;
        }

        $friendCode->update([
            'is_used' => true,
            'used_by_user_id' => $newUser->id,
        ]);

        return true;
    }

    public function getFriendCodeStats(User $user): array
    {
        $totalCodes = $user->friendCodes()->count();
        $usedCodes = $user->friendCodes()->where('is_used', true)->count();
        $activeCodes = $user->friendCodes()->where('is_used', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        return [
            'total' => $totalCodes,
            'used' => $usedCodes,
            'active' => $activeCodes,
            'referrals' => $this->getReferralCount($user),
        ];
    }

    public function getReferralCount(User $user): int
    {
        return FriendCode::where('user_id', $user->id)
            ->where('is_used', true)
            ->count();
    }

    public function cleanupExpiredCodes(): int
    {
        return FriendCode::where('is_used', false)
            ->where('expires_at', '<', now())
            ->delete();
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::random(32);
        } while (FriendCode::where('code', $code)->exists());

        return $code;
    }
}
