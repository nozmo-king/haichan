<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpReceipt extends Model
{
    use HasFactory;

    protected $primaryKey = 'client_op_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'client_op_id',
        'result_json',
    ];

    protected $casts = [
        'result_json' => 'array',
    ];
}