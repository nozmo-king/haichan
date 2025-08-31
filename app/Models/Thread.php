<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = ['board_id', 'title', 'content', 'author_name', 'user_id', 'image_path', 'image_filename'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAuthorDisplayName()
    {
        return $this->user ? substr($this->user->allowedPublicKey->public_key, 0, 8) . '...' : $this->author_name;
    }
}
