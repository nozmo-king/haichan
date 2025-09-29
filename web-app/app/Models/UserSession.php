<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $primaryKey = 'user_token';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_token',
        'friend_code_used',
        'username',
        'last_seen',
        'total_pow_score',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];
}
