<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'thread_id',
        'parent_id',
        'author_pubkey_hex',
        'title',
        'body',
        'attachments_json',
    ];

    protected $casts = [
        'attachments_json' => 'array',
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

    public function author()
    {
        return $this->belongsTo(User::class, 'author_pubkey_hex', 'pubkey_hex');
    }
}