<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PowV1Challenge extends Model
{
    use HasFactory;

    protected $table = 'pow_v1_challenges';

    protected $fillable = [
        'id',
        'user_pubkey_hex',
        'scope',
        'thread_id',
        'parent_id',
        'post_bytes_hash',
        'required_prefix_hex',
        'challenge_version',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_pubkey_hex', 'pubkey_hex');
    }
}
