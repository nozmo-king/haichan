<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = [
        'board_id', 'title', 'content', 'user_id', 'author_name',
        'image_path', 'image_filename', 'image_original_name', 'image_size',
        'reply_count', 'image_count', 'ip_address', 'poster_hash',
        'pow_nonce', 'pow_hash', 'pow_challenge_id', 'pow_pattern', 'pow_difficulty', 'pow_verified_at',
    ];

    protected $guarded = [
        'sticky', 'locked', 'bump_score', 'bumped_at',
    ];

    protected $casts = [
        'sticky' => 'boolean',
        'locked' => 'boolean',
        'reply_count' => 'integer',
        'image_count' => 'integer',
        'image_size' => 'integer',
        'bump_score' => 'integer',
        'bumped_at' => 'datetime',
        'pow_nonce' => 'integer',
        'pow_difficulty' => 'decimal:2',
        'pow_verified_at' => 'datetime',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bitcoinUser()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'asc');
    }

    public function proofOfWork()
    {
        return $this->hasMany(ProofOfWork::class, 'thread_id');
    }

    public function getTotalPowAttribute()
    {
        return $this->proofOfWork()->sum('points') + $this->bump_score;
    }

    public function getAccumulatedPointsAttribute()
    {
        // Real-time calculation of all PoW for this thread
        $threadPoW = ProofOfWork::where('thread_id', $this->id)->sum('points');
        $bumpScore = $this->bump_score ?? 0;
        $threadCreatePoW = $this->pow_difficulty ?? 1;

        return $threadPoW + $bumpScore + $threadCreatePoW;
    }

    public function getRealTimeHashrateAttribute()
    {
        // Calculate hashrate based on recent PoW submissions
        $recentProofs = ProofOfWork::where('thread_id', $this->id)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        return $recentProofs * 256; // Estimated hashes based on 21e8 difficulty
    }

    public function getCalculatedPowAttribute()
    {
        // REAL PoW calculation - no fake numbers
        $patternValues = [
            '21' => 0.1,
            '21e' => 0.5,
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 100,
            '000' => 500,
            '666' => 666,
            '777' => 777,
            'deadbeef' => 3133,
            '1337' => 1337,
        ];

        $totalPoints = 0;
        foreach ($patternValues as $pattern => $points) {
            $count = $this->proofOfWork()
                ->where('pattern', $pattern)
                ->count();
            $totalPoints += $count * $points;
        }

        return $totalPoints;
    }

    public function getUrlAttribute()
    {
        return "/{$this->board->name}/thread/{$this->id}";
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? "/storage/{$this->image_path}" : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->image_filename ? "/storage/thumbs/{$this->image_filename}" : null;
    }

    public function addReply(Post $post)
    {
        $this->increment('reply_count');

        if ($post->image_filename) {
            $this->increment('image_count');
        }

        if ($this->reply_count < 500) {
            $this->update(['bumped_at' => now()]);
        }

        $this->board->incrementPostCount();
    }

    public function getAuthorDisplayName()
    {
        return $this->author_name ?: 'Anonymous';
    }

    public static function generatePosterHash($ip, $threadId)
    {
        return substr(hash('sha256', $ip.$threadId.config('app.key', 'haichan')), 0, 8);
    }

    public static function generateChallenge()
    {
        return bin2hex(random_bytes(16));
    }

    public static function verifyProofOfWork($data, $nonce, $hash, $pattern)
    {
        $calculatedHash = hash('sha256', $data.':'.$nonce);

        if ($calculatedHash !== strtolower($hash)) {
            return ['valid' => false, 'error' => 'Hash mismatch'];
        }

        if (! str_starts_with(strtolower($calculatedHash), strtolower($pattern))) {
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
        $data = "thread:{$this->board->code}:{$this->title}:{$challengeId}";
        $verification = self::verifyProofOfWork($data, $nonce, $hash, $pattern);

        if (! $verification['valid']) {
            return $verification;
        }

        $this->update([
            'pow_nonce' => $nonce,
            'pow_hash' => $hash,
            'pow_challenge_id' => $challengeId,
            'pow_pattern' => $pattern,
            'pow_difficulty' => $this->calculateDifficulty($pattern),
            'pow_verified_at' => now(),
        ]);

        return ['valid' => true];
    }

    private function calculateDifficulty($pattern)
    {
        $difficulties = [
            '21' => 0.1,
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 100,
            '21e80000' => 500,
        ];

        return $difficulties[$pattern] ?? 1.0;
    }
}
