<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofSubmission extends Model
{
    protected $fillable = [
        'user_session',
        'target_type',
        'target_id',
        'pattern',
        'hash',
        'nonce',
        'challenge_data',
        'content_hash',
        'difficulty',
        'hashes_computed',
        'ip_address',
        'metadata'
    ];

    protected $casts = [
        'nonce' => 'integer',
        'difficulty' => 'decimal:2',
        'hashes_computed' => 'integer',
        'metadata' => 'array'
    ];

    public static function generateUserSession($ip)
    {
        return hash('sha256', $ip . time() . config('app.key'));
    }

    public static function recordProof($userSession, $targetType, $targetId, $pattern, $hash, $nonce, $challengeData, $contentHash = null, $ip = null, $metadata = [], $hashesComputed = null)
    {
        $difficulty = self::calculateDifficulty($pattern);

        // Estimate hash count if not provided (nonce gives minimum hashes required)
        $hashesComputed = $hashesComputed ?? $nonce;

        return self::create([
            'user_session' => $userSession,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'pattern' => $pattern,
            'hash' => $hash,
            'nonce' => $nonce,
            'challenge_data' => $challengeData,
            'content_hash' => $contentHash,
            'difficulty' => $difficulty,
            'hashes_computed' => $hashesComputed,
            'ip_address' => $ip,
            'metadata' => $metadata
        ]);
    }

    public static function calculateDifficulty($pattern)
    {
        $difficulties = [
            '21' => 0.1,
            '21e8' => 1.0,
            '21e80' => 5.0,
            '21e800' => 25.0,
            '21e8000' => 125.0,
            '000021e8' => 625.0
        ];

        return $difficulties[$pattern] ?? 1.0;
    }

    public static function getUserStats($userSession, $hours = 24)
    {
        return self::where('user_session', $userSession)
            ->where('created_at', '>=', now()->subHours($hours))
            ->selectRaw('
                COUNT(*) as total_proofs,
                COUNT(DISTINCT target_type) as target_types_mined,
                AVG(difficulty) as avg_difficulty,
                MAX(difficulty) as max_difficulty,
                MIN(created_at) as first_proof,
                MAX(created_at) as last_proof
            ')
            ->first();
    }

    public static function getBoardStats($boardCode)
    {
        return self::where('target_type', 'board')
            ->where('target_id', $boardCode)
            ->selectRaw('
                COUNT(*) as total_proofs,
                COUNT(DISTINCT user_session) as unique_miners,
                AVG(difficulty) as avg_difficulty,
                SUM(difficulty) as total_work
            ')
            ->first();
    }

    public static function getGlobalStats($hours = 24)
    {
        return self::where('created_at', '>=', now()->subHours($hours))
            ->selectRaw('
                COUNT(*) as total_proofs,
                COUNT(DISTINCT user_session) as active_miners,
                AVG(difficulty) as network_difficulty,
                SUM(difficulty) as total_work,
                COUNT(*) / ? as proofs_per_hour
            ', [$hours])
            ->first();
    }

    /**
     * Get accumulated points for a specific target
     * Points represent computational energy spent on this target
     */
    public static function getTargetPoints($targetType, $targetId)
    {
        return self::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->sum('difficulty');
    }

    /**
     * Get recent mining activity for a target (last 24 hours)
     * Used for determining "heat" or recent energy expenditure
     */
    public static function getTargetActivity($targetType, $targetId, $hours = 24)
    {
        return self::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('created_at', '>=', now()->subHours($hours))
            ->selectRaw('
                COUNT(*) as recent_proofs,
                SUM(difficulty) as recent_points,
                COUNT(DISTINCT user_session) as unique_miners
            ')
            ->first();
    }

    /**
     * Get total computational work (hashes) across all submissions
     */
    public static function getTotalHashes()
    {
        return self::sum('hashes_computed') ?: 0;
    }
}
