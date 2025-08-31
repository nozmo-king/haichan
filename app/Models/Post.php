<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['thread_id', 'content', 'author_name', 'parent_id', 'user_id', 'image_path', 'image_filename'];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
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
        return $this->replies()->with('allReplies');
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
