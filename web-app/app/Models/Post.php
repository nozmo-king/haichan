<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'parent_id',
        'user_id',
        'author_name',
        'content',
        'image_path',
        'image_filename',
        'image_hash',
        'ip_address',
        'country_flag',
        'pow_nonce',
        'pow_hash',
        'pow_challenge_id',
        'pow_pattern',
        'pow_difficulty',
        'pow_verified_at',
    ];

    protected $casts = [
        'attachments_json' => 'array',
    ];

    protected $keyType = 'int';
    public $incrementing = true;

    public function author()
    {
        return $this->belongsTo(User::class, 'author_pubkey_hex', 'pubkey_hex');
    }

    public function bitcoinUser()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id', 'id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function allReplies()
    {
        return $this->replies()->with('allReplies');
    }

    /**
     * Calculate accumulated points for this post
     */
    public function getAccumulatedPointsAttribute()
    {
        // Real-time calculation of all PoW for this post
        $postPoW = ProofOfWork::where('post_id', $this->id)->sum('points');
        $postCreatePoW = $this->pow_difficulty ?? 0;

        return $postPoW + $postCreatePoW;
    }

    /**
     * Generate tripcode from bitcoin address and username
     */
    public function getTripcode()
    {
        if (!$this->bitcoinUser) {
            return null;
        }

        $username = $this->bitcoinUser->username ?? 'Anon';
        $address = $this->bitcoinUser->bitcoin_address;
        
        // Create tripcode: hash(username + address) and take first 8 chars
        $tripcode = substr(hash('sha256', $username . $address), 0, 8);
        
        return $username . '!' . $tripcode;
    }
}