<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'pow_difficulty',
        'min_pow_points',
        'is_active',
        'is_public',
        'max_users',
        'message_rate_limit',
        'moderators',
    ];

    protected $casts = [
        'moderators' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->where('is_deleted', false);
    }

    public function allMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(BitcoinAuth::class, 'chat_room_users', 'chat_room_id', 'user_id')
                    ->withPivot([
                        'display_name',
                        'joined_at',
                        'last_seen_at',
                        'total_messages',
                        'total_pow_points',
                        'is_muted',
                        'muted_until',
                        'permissions'
                    ])
                    ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('last_seen_at', '>', now()->subMinutes(5));
    }

    public function recentMessages(int $limit = 50): HasMany
    {
        return $this->messages()
                    ->with(['user'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
    }

    public function canUserJoin($user): bool
    {
        if (!$this->is_active || !$this->is_public) {
            return false;
        }

        // Check if user has minimum PoW points
        $userTotalPow = $user->total_pow_points ?? 0;
        if ($userTotalPow < $this->min_pow_points) {
            return false;
        }

        // Check room capacity
        if ($this->activeUsers()->count() >= $this->max_users) {
            return false;
        }

        return true;
    }

    public function isUserModerator($user): bool
    {
        return in_array($user->id, $this->moderators ?? []);
    }

    public function getUserMessageCount($user, int $minutes = 1): int
    {
        return $this->messages()
                    ->where('user_id', $user->id)
                    ->where('created_at', '>', now()->subMinutes($minutes))
                    ->count();
    }

    public function canUserSendMessage($user): array
    {
        // Check if user is in room
        if (!$this->users()->where('user_id', $user->id)->exists()) {
            return ['can_send' => false, 'reason' => 'Not joined to room'];
        }

        // Check if user is muted
        $roomUser = $this->users()->where('user_id', $user->id)->first();
        if ($roomUser && $roomUser->pivot->is_muted) {
            $mutedUntil = $roomUser->pivot->muted_until;
            if (!$mutedUntil || now() < $mutedUntil) {
                return ['can_send' => false, 'reason' => 'User is muted'];
            }
        }

        // Check rate limiting
        $recentMessages = $this->getUserMessageCount($user, 1);
        if ($recentMessages >= $this->message_rate_limit) {
            return ['can_send' => false, 'reason' => 'Rate limited'];
        }

        return ['can_send' => true];
    }

    public function getStats(): array
    {
        return [
            'total_messages' => $this->messages()->count(),
            'active_users' => $this->activeUsers()->count(),
            'total_pow_points' => $this->messages()->sum('pow_points'),
            'avg_message_difficulty' => $this->messages()->avg('pow_points'),
            'recent_activity' => $this->messages()
                                      ->where('created_at', '>', now()->subHour())
                                      ->count(),
        ];
    }

    public static function getPublicRooms()
    {
        return static::where('is_active', true)
                     ->where('is_public', true)
                     ->withCount(['messages', 'activeUsers'])
                     ->orderBy('active_users_count', 'desc')
                     ->get();
    }

    public static function createDefaultRooms()
    {
        $defaultRooms = [
            [
                'name' => 'General Chat',
                'slug' => 'general',
                'description' => 'Main chat room for general discussion. Low PoW requirement.',
                'pow_difficulty' => '21e8',
                'min_pow_points' => 1,
                'message_rate_limit' => 10,
            ],
            [
                'name' => 'Mining Masters',
                'slug' => 'mining-masters',
                'description' => 'Elite chat for serious miners. Higher PoW requirements.',
                'pow_difficulty' => '21e',
                'min_pow_points' => 100,
                'message_rate_limit' => 5,
            ],
            [
                'name' => 'Hash Legends',
                'slug' => 'hash-legends',
                'description' => 'Exclusive room for top hashers. Prove your worth!',
                'pow_difficulty' => '777',
                'min_pow_points' => 1000,
                'message_rate_limit' => 3,
            ],
        ];

        foreach ($defaultRooms as $room) {
            static::firstOrCreate(['slug' => $room['slug']], $room);
        }
    }
}