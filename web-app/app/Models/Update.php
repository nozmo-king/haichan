<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'board_code',
        'message',
        'is_read'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }
    
    public static function createGlobalUpdate($message, $userId = 1)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'global',
            'board_code' => null,
            'message' => $message,
            'is_read' => false
        ]);
    }
    
    public static function createBoardUpdate($boardCode, $message, $userId = 1)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'local',
            'board_code' => $boardCode,
            'message' => $message,
            'is_read' => false
        ]);
    }
    
    public static function getGlobalUpdates($limit = 20)
    {
        return self::where('type', 'global')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public static function getBoardUpdates($boardCode, $limit = 20)
    {
        return self::where('type', 'local')
            ->where('board_code', $boardCode)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public static function getUnreadCount()
    {
        return self::where('is_read', false)->count();
    }
    
    public static function markAllAsRead()
    {
        return self::where('is_read', false)->update(['is_read' => true]);
    }
}