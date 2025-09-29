<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('bitcoin.auth');
    }

    /**
     * Display chat rooms list
     */
    public function index()
    {
        $rooms = ChatRoom::getPublicRooms();
        $user = auth()->user();
        
        // Get user's PoW points for room access checking
        $userPowPoints = $user->bitcoinUser?->accumulated_points ?? 0;
        
        return view('chat.index', compact('rooms', 'userPowPoints'));
    }

    /**
     * Show specific chat room
     */
    public function show(ChatRoom $room)
    {
        $user = auth()->user();
        
        // Check if user can join this room
        if (!$room->canUserJoin($user)) {
            $userPowPoints = $user->bitcoinUser?->accumulated_points ?? 0;
            
            return redirect()->route('chat.index')
                           ->with('error', "Cannot join room '{$room->name}'. Required: {$room->min_pow_points} PoW points. You have: {$userPowPoints}");
        }

        // Join user to room if not already joined
        $this->joinUserToRoom($room, $user);
        
        // Get recent messages
        $messages = ChatMessage::getRecentMessages($room, 50);
        
        // Get room stats
        $stats = $room->getStats();
        
        // Get active users
        $activeUsers = $room->activeUsers()->limit(20)->get();
        
        return view('chat.room', compact('room', 'messages', 'stats', 'activeUsers'));
    }

    /**
     * Send a message to chat room
     */
    public function sendMessage(Request $request, ChatRoom $room)
    {
        $user = auth()->user();
        
        // Validate input
        $validated = $request->validate([
            'message' => 'required|string|max:2000|min:1',
            'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
            'pow_nonce' => 'required|integer|min:0',
            'pow_challenge_id' => 'required|string|size:32|regex:/^[a-f0-9]{32}$/',
            'username' => 'nullable|string|max:50', // Optional display name
        ]);

        // Check if user can send message
        $canSend = $room->canUserSendMessage($user);
        if (!$canSend['can_send']) {
            return response()->json([
                'success' => false,
                'error' => $canSend['reason']
            ], 403);
        }

        // Rate limiting per user
        $key = "chat_message:{$user->id}:{$room->id}";
        if (RateLimiter::tooManyAttempts($key, $room->message_rate_limit)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'error' => "Rate limited. Try again in {$seconds} seconds."
            ], 429);
        }

        // Validate Proof of Work
        $powValidation = ChatMessage::validateProofOfWork([
            'message' => $validated['message'],
            'pow_hash' => $validated['pow_hash'],
            'pow_nonce' => $validated['pow_nonce'],
            'pow_challenge_id' => $validated['pow_challenge_id'],
            'room_difficulty' => $room->pow_difficulty,
        ]);

        if (!$powValidation['valid']) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid proof of work: ' . $powValidation['reason']
            ], 400);
        }

        try {
            // Create the message
            $message = ChatMessage::create([
                'chat_room_id' => $room->id,
                'user_id' => $user->id,
                'username' => $validated['username'] ?: null,
                'message' => $validated['message'],
                'message_hash' => hash('sha256', $validated['message'] . time()),
                'pow_hash' => $validated['pow_hash'],
                'pow_nonce' => $validated['pow_nonce'],
                'pow_pattern' => $powValidation['pattern'],
                'pow_points' => $powValidation['points'],
                'pow_challenge_id' => $validated['pow_challenge_id'],
                'ip_hash' => hash('sha256', $request->ip()),
            ]);

            // Update user stats in room
            $this->updateUserRoomStats($room, $user, $powValidation['points']);
            
            // Update user's total PoW points if they have a Bitcoin user record
            if ($user->bitcoinUser) {
                $user->bitcoinUser->increment('accumulated_points', $powValidation['points']);
            }

            // Hit rate limiter
            RateLimiter::hit($key, 60); // 1 minute decay

            // Create system message for rare patterns
            if ($message->isRarePattern()) {
                ChatMessage::createSystemMessage($room, 
                    "🎉 {$message->display_name} found a {$message->rarity_level} pattern: {$message->pow_pattern} (+{$powValidation['points']} points)!"
                );
            }

            Log::info('Chat message sent', [
                'user_id' => $user->id,
                'room_id' => $room->id,
                'pow_points' => $powValidation['points'],
                'pattern' => $powValidation['pattern']
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->formatted_message,
                    'username' => $message->display_name,
                    'pow_points' => $message->pow_points,
                    'pow_pattern' => $message->pow_pattern,
                    'rarity_level' => $message->rarity_level,
                    'rarity_color' => $message->rarity_color,
                    'created_at' => $message->created_at->format('H:i:s'),
                    'hash_preview' => substr($message->pow_hash, 0, 8),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Chat message creation failed', [
                'user_id' => $user->id,
                'room_id' => $room->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }

    /**
     * Get recent messages for a room (AJAX)
     */
    public function getMessages(ChatRoom $room, Request $request)
    {
        $user = auth()->user();
        
        // Update user's last seen time
        $this->updateUserLastSeen($room, $user);
        
        $limit = $request->get('limit', 50);
        $since = $request->get('since'); // Optional timestamp for incremental updates
        
        $query = ChatMessage::where('chat_room_id', $room->id)
                           ->where('is_deleted', false)
                           ->with(['user', 'user.bitcoinUser']);
        
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        
        $messages = $query->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get()
                         ->reverse()
                         ->values();
        
        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->formatted_message,
                    'username' => $message->display_name,
                    'pow_points' => $message->pow_points,
                    'pow_pattern' => $message->pow_pattern,
                    'rarity_level' => $message->rarity_level,
                    'rarity_color' => $message->rarity_color,
                    'is_system' => $message->is_system,
                    'created_at' => $message->created_at->format('H:i:s'),
                    'hash_preview' => substr($message->pow_hash, 0, 8),
                    'can_delete' => $message->canUserDelete(auth()->user()),
                ];
            })
        ]);
    }

    /**
     * Join user to room
     */
    public function joinRoom(ChatRoom $room)
    {
        $user = auth()->user();
        
        if (!$room->canUserJoin($user)) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot join this room'
            ], 403);
        }

        $this->joinUserToRoom($room, $user);
        
        // Create join system message
        $displayName = $user->bitcoinUser?->getDisplayName() ?? 'Anonymous';
        ChatMessage::createSystemMessage($room, "👋 {$displayName} joined the room");

        return response()->json(['success' => true]);
    }

    /**
     * Leave room
     */
    public function leaveRoom(ChatRoom $room)
    {
        $user = auth()->user();
        
        $room->users()->detach($user->id);
        
        // Create leave system message
        $displayName = $user->bitcoinUser?->getDisplayName() ?? 'Anonymous';
        ChatMessage::createSystemMessage($room, "👋 {$displayName} left the room");

        return response()->json(['success' => true]);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(ChatRoom $room, ChatMessage $message)
    {
        $user = auth()->user();
        
        if (!$message->canUserDelete($user)) {
            return response()->json([
                'success' => false,
                'error' => 'Permission denied'
            ], 403);
        }

        $message->softDelete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get room statistics
     */
    public function getRoomStats(ChatRoom $room)
    {
        return response()->json([
            'success' => true,
            'stats' => $room->getStats(),
            'active_users' => $room->activeUsers()->count(),
        ]);
    }

    /**
     * Private helper methods
     */
    private function joinUserToRoom(ChatRoom $room, $user): void
    {
        if (!$room->users()->where('user_id', $user->id)->exists()) {
            $room->users()->attach($user->id, [
                'display_name' => $user->bitcoinUser?->getDisplayName(),
                'joined_at' => now(),
                'last_seen_at' => now(),
            ]);
        } else {
            // Update last seen
            $this->updateUserLastSeen($room, $user);
        }
    }

    private function updateUserLastSeen(ChatRoom $room, $user): void
    {
        $room->users()->updateExistingPivot($user->id, [
            'last_seen_at' => now()
        ]);
    }

    private function updateUserRoomStats(ChatRoom $room, $user, int $powPoints): void
    {
        $room->users()->updateExistingPivot($user->id, [
            'total_messages' => \DB::raw('total_messages + 1'),
            'total_pow_points' => \DB::raw("total_pow_points + {$powPoints}"),
            'last_seen_at' => now()
        ]);
    }
}