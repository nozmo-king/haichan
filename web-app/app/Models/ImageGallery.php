<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageGallery extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'hash',
        'file_path',
        'file_size',
        'mime_type',
        'width',
        'height',
        'total_pow_earned',
        'usage_count',
    ];
}
