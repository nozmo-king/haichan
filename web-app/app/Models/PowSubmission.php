<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowSubmission extends Model
{
    protected $fillable = [
        'thread_id',
        'user_token',
        'nonce',
        'hash',
        'difficulty_prefix',
        'mining_duration_ms',
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
