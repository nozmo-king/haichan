<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BitcoinAuth;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Personal21e8Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserToolbarController extends Controller
{
    public function getToolbarData(Request $request)
    {
        $userId = session('bitcoin_auth_id');
        \Log::info('Toolbar API: Session data', [
            'bitcoin_auth_id' => $userId,
            'session_id' => session()->getId(),
            'all_session' => session()->all()
        ]);
        
        if (!$userId) {
            // Try to get from Sanctum token if session not available
            $authUser = $request->user();
            if ($authUser) {
                $userId = $authUser->id;
                \Log::info('Toolbar API: Using Sanctum auth', ['user_id' => $userId]);
            }
        }
        
        if (!$userId) {
            \Log::info('Toolbar API: No authentication found');
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = BitcoinAuth::find($userId);
        if (!$user) {
            \Log::error('Toolbar API: User not found', ['user_id' => $userId]);
            return response()->json(['error' => 'User not found'], 404);
        }
        
        \Log::info('Toolbar API: User found', [
            'user_id' => $user->id,
            'username' => $user->username,
            'total_pow_points' => $user->total_pow_points
        ]);

        // Get the user's highest 21e8 achievement level
        $personal21e8Level = null;
        if ($user->personal_21e8_hash) {
            $hash = strtolower($user->personal_21e8_hash);
            // Check from highest to lowest level
            if (str_starts_with($hash, '21e80000')) {
                $personal21e8Level = '21e80000';
            } elseif (str_starts_with($hash, '21e8000')) {
                $personal21e8Level = '21e8000';
            } elseif (str_starts_with($hash, '21e800')) {
                $personal21e8Level = '21e800';
            } elseif (str_starts_with($hash, '21e80')) {
                $personal21e8Level = '21e80';
            } elseif (str_starts_with($hash, '21e8')) {
                $personal21e8Level = '21e8';
            }
        }

        // Count recent threads (last 30 days)
        $recentThreadsCount = Thread::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Also count threads where user has posted (recent activity)
        $recentActivityCount = Post::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('thread_id')
            ->count('thread_id');

        $totalRecentActivity = $recentThreadsCount + $recentActivityCount;

        return response()->json([
            'username' => $user->username,
            'display_name' => $user->display_name ?: $user->username,
            'is_admin' => $user->is_admin,
            'is_moderator' => $user->is_moderator,
            'total_pow_points' => $user->total_pow_points,
            'personal_21e8_level' => $personal21e8Level,
            'recent_threads_count' => $totalRecentActivity,
            'user_id' => $user->id,
            'level' => $user->level,
        ]);
    }

    public function getRecentThreads(Request $request)
    {
        $userId = session('bitcoin_auth_id');
        
        if (!$userId) {
            $authUser = $request->user();
            if ($authUser) {
                $userId = $authUser->id;
            }
        }
        
        if (!$userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = BitcoinAuth::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get threads created by user (last 30 days)
        $createdThreads = Thread::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->with('board')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'board_code' => $thread->board ? $thread->board->code : 'general',
                    'created_at' => $thread->created_at->diffForHumans(),
                    'reply_count' => $thread->reply_count ?? 0,
                    'type' => 'created'
                ];
            });

        // Get recent posts by user to find threads they've replied to
        $userPosts = Post::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['thread', 'thread.board'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        $repliedThreads = collect();
        foreach ($userPosts as $post) {
            if ($post->thread && $post->thread->user_id != $user->id) {
                $repliedThreads->push([
                    'id' => $post->thread->id,
                    'title' => $post->thread->title,
                    'board_code' => $post->thread->board ? $post->thread->board->code : 'general',
                    'created_at' => $post->created_at->diffForHumans(),
                    'reply_count' => $post->thread->reply_count ?? 0,
                    'type' => 'replied'
                ]);
            }
        }
        
        // Remove duplicates by thread ID
        $repliedThreads = $repliedThreads->unique('id')->take(10);

        // Combine and sort by activity
        $allThreads = $createdThreads->merge($repliedThreads)
            ->sortByDesc(function($thread) {
                return strtotime($thread['created_at']);
            })
            ->take(15)
            ->values();

        return response()->json($allThreads);
    }
}