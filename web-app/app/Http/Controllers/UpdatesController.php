<?php

namespace App\Http\Controllers;

use App\Models\Update;
use Illuminate\Http\Request;

class UpdatesController extends Controller
{
    public function __construct()
    {
        // Only admin (user_id = 1) can post/delete updates
        $this->middleware(function ($request, $next) {
            if ($request->isMethod('post') || $request->isMethod('delete')) {
                if (session('bitcoin_auth_id') !== 1) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
            return $next($request);
        });
    }
    
    public function getGlobalUpdates()
    {
        $updates = Update::getGlobalUpdates();
        Update::markAllAsRead();
        
        return response()->json([
            'updates' => $updates
        ]);
    }
    
    public function getBoardUpdates($boardCode)
    {
        $updates = Update::getBoardUpdates($boardCode);
        
        return response()->json([
            'updates' => $updates
        ]);
    }
    
    public function postUpdate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:global,local',
            'board_code' => 'required_if:type,local',
            'message' => 'required|string|max:1000'
        ]);
        
        if ($request->type === 'global') {
            $update = Update::createGlobalUpdate(
                $request->message,
                session('bitcoin_auth_id')
            );
        } else {
            $update = Update::createBoardUpdate(
                $request->board_code,
                $request->message,
                session('bitcoin_auth_id')
            );
        }
        
        return response()->json([
            'success' => true,
            'update' => $update
        ]);
    }
    
    public function deleteUpdate($id)
    {
        $update = Update::find($id);
        
        if (!$update) {
            return response()->json(['error' => 'Update not found'], 404);
        }
        
        $update->delete();
        
        return response()->json([
            'success' => true
        ]);
    }
    
    public function getUnreadCount()
    {
        return response()->json([
            'count' => Update::getUnreadCount()
        ]);
    }
}