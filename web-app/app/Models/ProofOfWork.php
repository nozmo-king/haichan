<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfWork extends Model
{
    protected $fillable = [
        'thread_id', 'hash', 'nonce', 'data', 
        'pattern', 'points', 'verified_at', 'ip_address'
    ];

    protected $casts = [
        'nonce' => 'integer',
        'points' => 'decimal:2',
        'verified_at' => 'datetime'
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
