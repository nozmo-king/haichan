<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PowCommit extends Model
{
    use HasFactory;

    protected $table = 'pow_commits';

    protected $fillable = [
        'id',
        'challenge_id',
        'nonce_u64',
        'miner_version',
        'timestamp_i64',
        'solved_hash_hex',
        'accepted',
        'reject_reason',
        'solve_time_ms',
    ];

    protected $casts = [
        'accepted' => 'boolean',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    public function challenge()
    {
        return $this->belongsTo(PowChallenge::class, 'challenge_id');
    }
}