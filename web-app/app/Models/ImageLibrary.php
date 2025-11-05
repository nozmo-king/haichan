<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageLibrary extends Model
{
    protected $table = 'image_library';
    
    protected $fillable = [
        'hash',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'width',
        'height',
        'created_at',
        'updated_at',
        'first_thread_id',
        'first_post_id',
        'uploader_id',
        'upload_ip',
    ];
    
    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];
    
    public function firstThread()
    {
        return $this->belongsTo(Thread::class, 'first_thread_id');
    }
    
    public function firstPost()
    {
        return $this->belongsTo(Post::class, 'first_post_id');
    }
    
    public function uploader()
    {
        return $this->belongsTo(BitcoinAuth::class, 'uploader_id');
    }
}
