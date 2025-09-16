<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'thread_id', 'content', 'user_id', 'author_name', 'parent_id',
        'image_path', 'image_filename',
        'ip_address', 'poster_hash',
        'pow_nonce', 'pow_hash', 'pow_challenge_id', 'pow_pattern', 'pow_difficulty', 'pow_verified_at'
    ];

    protected $casts = [
        'image_size' => 'integer',
        'pow_nonce' => 'integer',
        'pow_difficulty' => 'decimal:2',
        'pow_verified_at' => 'datetime'
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function allReplies()
    {
        return $this->replies()->with('allReplies')->orderBy('created_at', 'asc');
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? "/storage/{$this->image_path}" : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->image_filename ? "/storage/thumbs/{$this->image_filename}" : null;
    }

    public function getAuthorDisplayName()
    {
        return $this->author_name ?: 'Anonymous';
    }

    public function getFormattedContentAttribute()
    {
        $content = htmlspecialchars($this->content);
        
        // Convert >>123456 to links
        $content = preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', $content);
        
        // Convert >greentext
        $content = preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>', $content);
        
        // Convert line breaks
        $content = nl2br($content);
        
        return $content;
    }

    public static function generateChallenge()
    {
        return bin2hex(random_bytes(16));
    }

    public static function verifyProofOfWork($data, $nonce, $hash, $pattern)
    {
        // Frontend includes nonce in the hash calculation: challengeData + ':' + nonce
        $fullData = $data . ':' . $nonce;
        $calculatedHash = hash('sha256', $fullData);

        if ($calculatedHash !== strtolower($hash)) {
            return ['valid' => false, 'error' => 'Hash mismatch'];
        }

        if (!str_starts_with(strtolower($calculatedHash), strtolower($pattern))) {
            return ['valid' => false, 'error' => 'Pattern mismatch'];
        }

        return ['valid' => true];
    }

    public function requiresProofOfWork()
    {
        return empty($this->pow_hash) || empty($this->pow_verified_at);
    }

    public function validateAndSetProofOfWork($nonce, $hash, $challengeId, $pattern = '21e8')
    {
        $data = "post:{$this->thread_id}:{$this->content}:{$challengeId}";
        $verification = self::verifyProofOfWork($data, $nonce, $hash, $pattern);
        
        if (!$verification['valid']) {
            return $verification;
        }
        
        $this->update([
            'pow_nonce' => $nonce,
            'pow_hash' => $hash,
            'pow_challenge_id' => $challengeId,
            'pow_pattern' => $pattern,
            'pow_difficulty' => $this->calculateDifficulty($pattern),
            'pow_verified_at' => now()
        ]);
        
        return ['valid' => true];
    }
    
    private function calculateDifficulty($pattern)
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
}
