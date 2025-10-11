<?php

namespace App\Services;

use App\Models\Challenge;
use Illuminate\Support\Facades\Log;

class ChallengeVerifier
{
    public function verifyChallenge(string $token, string $clientNonce, string $hash): array
    {
        $challenge = Challenge::where('token', $token)->first();

        if (!$challenge) {
            return [
                'valid' => false,
                'error' => 'Challenge not found',
            ];
        }

        if ($challenge->isExpired()) {
            return [
                'valid' => false,
                'error' => 'Challenge expired',
            ];
        }

        if ($challenge->isUsed()) {
            return [
                'valid' => false,
                'error' => 'Challenge already used',
            ];
        }

        if (!$this->verifyHmacSignature($challenge)) {
            return [
                'valid' => false,
                'error' => 'Invalid challenge signature',
            ];
        }

        $canonicalPayloadJson = json_encode($challenge->canonical_payload, JSON_UNESCAPED_SLASHES);
        $hashInput = $canonicalPayloadJson . ':' . $clientNonce;
        $computedHash = hash('sha256', $hashInput);

        Log::info('Challenge verification', [
            'token' => $token,
            'canonical_payload' => $canonicalPayloadJson,
            'client_nonce' => $clientNonce,
            'hash_input' => $hashInput,
            'computed_hash' => $computedHash,
            'submitted_hash' => $hash,
        ]);

        if ($computedHash !== $hash) {
            return [
                'valid' => false,
                'error' => 'Hash verification failed',
                'computed_hash' => $computedHash,
                'submitted_hash' => $hash,
            ];
        }

        $difficulty = $challenge->difficulty;
        if (!str_starts_with(strtolower($hash), strtolower($difficulty))) {
            return [
                'valid' => false,
                'error' => 'Hash does not match difficulty pattern',
            ];
        }

        return [
            'valid' => true,
            'challenge' => $challenge,
        ];
    }

    public function consumeChallenge(string $token): bool
    {
        $challenge = Challenge::where('token', $token)->first();

        if (!$challenge) {
            return false;
        }

        $challenge->markAsUsed();
        return true;
    }

    public function verifyHmacSignature(Challenge $challenge): bool
    {
        $computedSignature = $this->computeHmacSignature($challenge->canonical_payload);
        return hash_equals($computedSignature, $challenge->hmac_signature);
    }

    public function computeHmacSignature(array $canonicalPayload): string
    {
        $canonicalPayloadJson = json_encode($canonicalPayload, JSON_UNESCAPED_SLASHES);
        $appKey = config('app.key');
        
        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return hash_hmac('sha256', $canonicalPayloadJson, $appKey);
    }

    public function cleanupExpiredChallenges(): int
    {
        return Challenge::where('expires_at', '<', now())
            ->whereNull('used_at')
            ->delete();
    }

    public function getDifficultyValue(string $pattern): int
    {
        return strlen($pattern);
    }

    public function getMinimumDifficulty(string $action, string $targetType): array
    {
        // All actions use 21e8 difficulty for now
        $minimumPattern = '21e8';
        $minimumValue = $this->getDifficultyValue($minimumPattern);

        return [
            'pattern' => $minimumPattern,
            'value' => $minimumValue,
        ];
    }
}
