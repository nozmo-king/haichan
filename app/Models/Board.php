<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = ['code', 'name', 'description'];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }
}
