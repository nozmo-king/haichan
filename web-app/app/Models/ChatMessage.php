<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'username',
        'message',
        'message_hash',
        'pow_hash',
        'pow_nonce',
        'pow_pattern',
        'pow_points',
        'pow_challenge_id',
        'ip_hash',
        'is_system',
        'is_deleted',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_system' => 'boolean',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_system) {
            return 'System';
        }

        if ($this->username) {
            return $this->username;
        }

        if ($this->user && $this->user->bitcoinUser) {
            return $this->user->bitcoinUser->getDisplayName();
        }

        return 'Anonymous';
    }

    public function getRarityLevelAttribute(): string
    {
        return match ($this->pow_pattern) {
            'deadbeef' => 'LEGENDARY',
            '1337' => 'ELITE', 
            '777' => 'LUCKY',
            '666' => 'CURSED',
            '000' => 'RARE',
            '111' => 'RARE',
            '21e' => 'UNCOMMON',
            '21e8' => 'COMMON',
            default => 'BASIC'
        };
    }

    public function getRarityColorAttribute(): string
    {
        return match ($this->rarity_level) {
            'LEGENDARY' => '#FFD700',
            'ELITE' => '#FF6B35',
            'LUCKY' => '#FFD700',
            'CURSED' => '#FF4444',
            'RARE' => '#00FFFF',
            'UNCOMMON' => '#9370DB',
            'COMMON' => '#9fd971',
            default => '#666666'
        };
    }

    public function isRarePattern(): bool
    {
        return in_array($this->pow_pattern, ['777', '666', '000', '111', 'deadbeef', '1337']);
    }

    public function getFormattedMessageAttribute(): string
    {
        $message = htmlspecialchars($this->message);
        
        // Replace >>hash references with links
        $message = preg_replace_callback('/>>([a-f0-9]{8,16})/i', function ($matches) {
            $hash = $matches[1];
            $referencedMessage = static::where('pow_hash', 'like', $hash . '%')->first();
            
            if ($referencedMessage) {
                return "<span class=\"message-reference\" data-message-id=\"{$referencedMessage->id}\">>>{$hash}</span>";
            }
            
            return $matches[0];
        }, $message);

        // Convert URLs to links
        $message = preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" rel="noopener">$1</a>', $message);

        return $message;
    }

    public function canUserDelete($user): bool
    {
        // User can delete their own messages
        if ($this->user_id === $user->id) {
            return true;
        }

        // Moderators can delete any message
        if ($this->chatRoom && $this->chatRoom->isUserModerator($user)) {
            return true;
        }

        return false;
    }

    public static function createSystemMessage(ChatRoom $room, string $message, array $metadata = []): self
    {
        return static::create([
            'chat_room_id' => $room->id,
            'message' => $message,
            'message_hash' => hash('sha256', $message . time()),
            'pow_hash' => str_repeat('0', 64), // System messages don't need real PoW
            'pow_nonce' => 0,
            'pow_pattern' => 'system',
            'pow_points' => 0,
            'pow_challenge_id' => 'system',
            'is_system' => true,
            'metadata' => $metadata,
        ]);
    }

    public static function validateProofOfWork(array $data): array
    {
        $message = $data['message'] ?? '';
        $nonce = $data['pow_nonce'] ?? 0;
        $hash = $data['pow_hash'] ?? '';
        $challengeId = $data['pow_challenge_id'] ?? '';
        $roomDifficulty = $data['room_difficulty'] ?? '21e8';

        // Recreate the challenge data
        $challengeData = "chat:{$message}:{$challengeId}:{$nonce}";
        
        // Verify the hash
        $calculatedHash = hash('sha256', $challengeData);
        
        if ($calculatedHash !== $hash) {
            return ['valid' => false, 'reason' => 'Hash verification failed'];
        }

        // Check if hash meets difficulty requirement
        if (!str_starts_with($hash, strtolower($roomDifficulty))) {
            return ['valid' => false, 'reason' => 'Hash does not meet difficulty requirement'];
        }

        // Determine points based on pattern rarity
        $points = static::calculatePoints($hash);
        
        return [
            'valid' => true,
            'points' => $points,
            'pattern' => static::getPatternFromHash($hash),
            'hash' => $hash,
        ];
    }

    protected static function calculatePoints(string $hash): int
    {
        // Check for rare patterns first (higher points)
        if (str_starts_with($hash, 'deadbeef')) return 5000;
        if (str_starts_with($hash, '1337')) return 2500;
        if (str_starts_with($hash, '777')) return 777;
        if (str_starts_with($hash, '666')) return 666;
        if (str_starts_with($hash, '000')) return 500;
        if (str_starts_with($hash, '111')) return 400;
        if (str_starts_with($hash, '21e')) return 10;
        if (str_starts_with($hash, '21e8')) return 1;

        return 1; // Default points
    }

    protected static function getPatternFromHash(string $hash): string
    {
        if (str_starts_with($hash, 'deadbeef')) return 'deadbeef';
        if (str_starts_with($hash, '1337')) return '1337';
        if (str_starts_with($hash, '777')) return '777';
        if (str_starts_with($hash, '666')) return '666';
        if (str_starts_with($hash, '000')) return '000';
        if (str_starts_with($hash, '111')) return '111';
        if (str_starts_with($hash, '21e')) return '21e';
        if (str_starts_with($hash, '21e8')) return '21e8';

        return 'unknown';
    }

    public static function getRecentMessages(ChatRoom $room, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('chat_room_id', $room->id)
                    ->where('is_deleted', false)
                    ->with(['user', 'user.bitcoinUser'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->reverse()
                    ->values();
    }

    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();
        return $this->save();
    }
}