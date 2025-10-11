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
        // Middleware is handled by route groups in web.php
    }

    /**
     * Display default general chat (auto-redirect)
     */
    public function index()
    {
        // Get or create the default general chat room
        $generalRoom = ChatRoom::firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General',
                'description' => 'Default general discussion',
                'pow_difficulty' => '21e8',
                'min_pow_points' => 0,
                'is_active' => true,
                'is_public' => true,
                'max_users' => 100,
                'message_rate_limit' => 60
            ]
        );
        
        // Create hidden admin room (accessible only via /join #sadmin)
        ChatRoom::firstOrCreate(
            ['slug' => 'sadmin'],
            [
                'name' => 'Secret Admin',
                'description' => 'Hidden admin chat',
                'pow_difficulty' => '21e8',
                'min_pow_points' => 1000,
                'is_active' => true,
                'is_public' => false,
                'max_users' => 10,
                'message_rate_limit' => 30
            ]
        );
        
        // Auto-redirect to general chat
        return redirect()->route('chat.room', ['room' => $generalRoom]);
    }

    /**
     * Show specific chat room
     */
    public function show(ChatRoom $room)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return redirect('/auth/login')->withErrors(['auth' => 'Please log in to access chat']);
        }
        
        // Join user to room if not already joined
        $this->joinUserToRoom($room, $user);
        
        // Get recent messages (simplified)
        $messages = $room->messages()->with('user')->latest()->take(50)->get()->reverse();
        
        return view('chat.room', compact('room', 'messages', 'user'));
    }

    /**
     * Send message with PoW validation
     */
    public function sendMessage(Request $request, ChatRoom $room)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        // Validate input (POW disabled)
        $validated = $request->validate([
            'message' => 'required|string|max:500|min:1',
            // 'nonce' => 'required|string',
            // 'hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
        ]);

        $message = trim($validated['message']);
        
        // Check for commands (skip PoW validation for commands)
        if (str_starts_with($message, '/')) {
            return $this->handleCommand($request, $room, $user, $validated);
        }

        try {
            // POW disabled - simplified chat
            
            // Get user's display name from room or generate default
            $roomUser = $room->users()->where('user_id', $user->id)->first();
            $displayName = $roomUser?->pivot?->display_name ?? 
                          ($user->username . ' !' . substr(hash('sha256', $user->address), 0, 6));

            // Create the message
            $message = ChatMessage::create([
                'chat_room_id' => $room->id,
                'user_id' => $user->id,
                'username' => $displayName,
                'message' => $validated['message'],
                'message_hash' => hash('sha256', $validated['message']),
                'ip_hash' => hash('sha256', $request->ip()),
                // POW fields disabled
                // 'pow_hash' => null,
                // 'pow_nonce' => null,
                // 'pow_pattern' => null,
                // 'pow_points' => 0,
                // 'pow_challenge_id' => null,
            ]);
            
            // Update user's last seen time
            $room->users()->updateExistingPivot($user->id, [
                'last_seen_at' => now(),
            ]);
            
            // Return the created message with formatted time
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'username' => $displayName,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Chat message creation failed', [
                'user_id' => $user->id,
                'room_id' => $room->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages for room
     */
    public function getMessages(Request $request, ChatRoom $room)
    {
        $afterId = $request->get('after', 0);
        
        $query = $room->messages()->with(['user']);
        
        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        } else {
            $query->latest()->take(50);
        }
        
        $messages = $query->get();
        
        if ($afterId == 0) {
            $messages = $messages->reverse()->values();
        }

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'username' => $message->username ?? $message->user->username ?? $message->user->address ?? 'Anonymous',
                    'created_at' => $message->created_at->format('H:i:s'),
                    'hash_preview' => substr($message->pow_hash ?? '', 0, 8),
                    'points' => $message->pow_points ?? 1,
                    'pattern' => $message->pow_pattern ?? 'basic',
                ];
            })
        ]);
    }

    /**
     * Join room
     */
    public function joinRoom(ChatRoom $room)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $this->joinUserToRoom($room, $user);
        
        return response()->json(['success' => true]);
    }

    /**
     * Leave room
     */
    public function leaveRoom(ChatRoom $room)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $room->users()->detach($user->id);
        
        return response()->json(['success' => true]);
    }

    /**
     * Get room stats
     */
    public function getRoomStats(ChatRoom $room)
    {
        return response()->json([
            'users_online' => $room->users()->count(),
            'messages_today' => $room->messages()->whereDate('created_at', today())->count(),
        ]);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, ChatRoom $room, ChatMessage $message)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        if (!$message->canUserDelete($user)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this message'
            ], 403);
        }
        
        $message->softDelete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Private helper to join user to room
     */
    private function joinUserToRoom(ChatRoom $room, $user): void
    {
        if (!$room->users()->where('user_id', $user->id)->exists()) {
            $room->users()->attach($user->id, [
                'display_name' => $user->username ?? $user->address ?? 'Anonymous',
                'joined_at' => now(),
                'last_seen_at' => now(),
            ]);
        }
    }

    /**
     * Calculate points based on hash pattern
     */
    private function calculatePoints(string $hash): int
    {
        if (str_starts_with($hash, 'deadbeef')) return 5000;
        if (str_starts_with($hash, '1337')) return 2500;
        if (str_starts_with($hash, '777')) return 777;
        if (str_starts_with($hash, '666')) return 666;
        if (str_starts_with($hash, '000')) return 500;
        if (str_starts_with($hash, '111')) return 400;
        if (str_starts_with($hash, '21e')) return 10;
        if (str_starts_with($hash, '21')) return 5;
        return 1;
    }

    /**
     * Get pattern from hash
     */
    private function getPatternFromHash(string $hash): string
    {
        if (str_starts_with($hash, 'deadbeef')) return 'deadbeef';
        if (str_starts_with($hash, '1337')) return '1337';
        if (str_starts_with($hash, '777')) return '777';
        if (str_starts_with($hash, '666')) return '666';
        if (str_starts_with($hash, '000')) return '000';
        if (str_starts_with($hash, '111')) return '111';
        if (str_starts_with($hash, '21e')) return '21e';
        if (str_starts_with($hash, '21')) return '21';
        return 'basic';
    }

    /**
     * Calculate hashrate estimate
     */
    private function calculateHashrate(string $nonce): int
    {
        $nonceInt = (int)$nonce;
        return $nonceInt > 0 ? min($nonceInt * 100, 999999) : 1000;
    }

    /**
     * Set user's nickname for this room
     */
    public function setNickname(Request $request, ChatRoom $room)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $validated = $request->validate([
            'nickname' => 'required|string|max:20|min:1',
        ]);
        
        $nickname = $validated['nickname'];
        
        // Generate tripcode from user address (first 6 chars)
        $tripcode = '!' . substr(hash('sha256', $user->address), 0, 6);
        $displayName = $nickname . ' ' . $tripcode;
        
        // Update user's display name in this room
        $room->users()->updateExistingPivot($user->id, [
            'display_name' => $displayName,
            'last_seen_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'display_name' => $displayName,
        ]);
    }
    
    /**
     * Get online users for room
     */
    public function getOnlineUsers(ChatRoom $room)
    {
        $users = $room->users()
                     ->wherePivot('last_seen_at', '>', now()->subMinutes(5))
                     ->orderByPivot('last_seen_at', 'desc')
                     ->get()
                     ->map(function ($user) {
                         return [
                             'id' => $user->id,
                             'display_name' => $user->pivot->display_name ?? ($user->username . ' !' . substr(hash('sha256', $user->address), 0, 6)),
                             'last_seen' => $user->pivot->last_seen_at ? $user->pivot->last_seen_at->diffForHumans() : 'just now',
                         ];
                     });
        
        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Handle chat commands
     */
    private function handleCommand(Request $request, ChatRoom $room, $user, array $validated)
    {
        $message = trim($validated['message']);
        $command = strtolower(explode(' ', $message)[0]);
        
        switch ($command) {
            case '/clear':
                return response()->json([
                    'success' => false,
                    'error' => 'The /clear command has been disabled.'
                ], 400);
                
            case '/help':
                return $this->handleHelpCommand($room, $user);
                
            default:
                return response()->json([
                    'success' => false,
                    'error' => 'Unknown command: ' . $command . '. Type /help for available commands.'
                ], 400);
        }
    }
    
    /**
     * Handle /clear command
     */
    private function handleClearCommand(ChatRoom $room, $user)
    {
        // Check if user is moderator or admin
        if (!$room->isUserModerator($user)) {
            return response()->json([
                'success' => false,
                'error' => 'Only moderators can clear the chat.'
            ], 403);
        }
        
        try {
            // Soft delete all messages in the room
            $deletedCount = $room->messages()->update([
                'is_deleted' => true,
                'deleted_at' => now(),
            ]);
            
            // Create a system message about the clear
            $displayName = $this->getUserDisplayName($room, $user);
            ChatMessage::createSystemMessage($room, "💨 Chat cleared by {$displayName} ({$deletedCount} messages removed)");
            
            return response()->json([
                'success' => true,
                'action' => 'clear_chat',
                'message' => "Chat cleared successfully. {$deletedCount} messages removed.",
                'deleted_count' => $deletedCount,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to clear chat', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear chat. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Handle /help command
     */
    private function handleHelpCommand(ChatRoom $room, $user)
    {
        $commands = [
            '/help - Show this help message',
        ];
        
        // Add moderator commands if user is moderator
        if ($room->isUserModerator($user)) {
            $commands[] = '(No moderator commands available)';
        }
        
        $helpText = "📋 **Available Commands:**\n" . implode("\n", $commands);
        
        // Create a temporary system message just for this user
        return response()->json([
            'success' => true,
            'action' => 'help',
            'message' => $helpText,
        ]);
    }
    
    /**
     * Get user's display name for this room
     */
    private function getUserDisplayName(ChatRoom $room, $user): string
    {
        $roomUser = $room->users()->where('user_id', $user->id)->first();
        return $roomUser?->pivot?->display_name ?? 
               ($user->username . ' !' . substr(hash('sha256', $user->address), 0, 6));
    }

    /**
     * Get the current authenticated BitcoinAuth user
     */
    private function getBitcoinAuthUser()
    {
        $userId = session('bitcoin_auth_id');
        
        if (!$userId || !is_numeric($userId)) {
            return null;
        }
        
        return \App\Models\BitcoinAuth::find($userId);
    }

    /**
     * Handle chat slash commands (/join, etc.)
     */
    public function executeCommand(Request $request, ChatRoom $room)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $validated = $request->validate([
            'command' => 'required|string|max:100',
        ]);
        
        $command = trim($validated['command']);
        
        // Parse /join #roomname command
        if (preg_match('/^\/join #(\w+)$/', $command, $matches)) {
            $roomSlug = $matches[1];
            
            // Special handling for secret admin room
            if ($roomSlug === 'sadmin') {
                $adminRoom = ChatRoom::where('slug', 'sadmin')->first();
                
                if ($adminRoom && $user->accumulated_points >= 1000) {
                    $this->joinUserToRoom($adminRoom, $user);
                    return response()->json([
                        'success' => true,
                        'action' => 'redirect',
                        'url' => route('chat.room', ['room' => $adminRoom])
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Access denied. Insufficient privileges.'
                    ]);
                }
            }
            
            // Regular room join
            $targetRoom = ChatRoom::where('slug', $roomSlug)
                                 ->where('is_public', true)
                                 ->where('is_active', true)
                                 ->first();
                                 
            if ($targetRoom) {
                $this->joinUserToRoom($targetRoom, $user);
                return response()->json([
                    'success' => true,
                    'action' => 'redirect',
                    'url' => route('chat.room', ['room' => $targetRoom])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Room "' . $roomSlug . '" not found or private'
                ]);
            }
        }
        
        // /hp command (show total site hashpower)
        if (preg_match('/^\/hp$/', $command)) {
            $totalSitePoints = \App\Models\BitcoinAuth::sum('accumulated_points') ?? 0;
            $totalUsers = \App\Models\BitcoinAuth::count();
            $avgPoints = $totalUsers > 0 ? round($totalSitePoints / $totalUsers) : 0;
            
            return response()->json([
                'success' => true,
                'action' => 'system_message',
                'message' => "🌐 Total site hashpower: {$totalSitePoints} points<br>" .
                           "👥 Active miners: {$totalUsers}<br>" .
                           "📊 Average: {$avgPoints} points per user"
            ]);
        }
        
        // /hp -u [username] command (show specific user hashpower)
        if (preg_match('/^\/hp -u (.+)$/', $command, $matches)) {
            $targetUsername = trim($matches[1]);
            $targetUser = \App\Models\BitcoinAuth::where('username', $targetUsername)->first();
            
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'error' => "User '{$targetUsername}' not found"
                ]);
            }
            
            $targetPoints = $targetUser->accumulated_points ?? 0;
            $targetRank = $this->calculateRank($targetPoints);
            $userPoints = $user->accumulated_points ?? 0;
            $userRank = $this->calculateRank($userPoints);
            
            return response()->json([
                'success' => true,
                'action' => 'system_message',
                'message' => "👤 {$targetUsername}: {$targetPoints} points ({$targetRank})<br>" .
                           "⚡ Your power: {$userPoints} points ({$userRank})"
            ]);
        }
        
        // /mine command - mine last post or specific post above
        if (preg_match('/^\/mine(\^*)$/', $command, $matches)) {
            $caretsCount = strlen($matches[1] ?? '');
            $positionsBack = max(1, $caretsCount); // Default to 1 if no carets
            
            // Get the target message (N positions back from current)
            $targetMessage = $room->messages()
                ->orderBy('id', 'desc')
                ->skip($positionsBack - 1)
                ->first();
                
            if (!$targetMessage) {
                return response()->json([
                    'success' => false,
                    'error' => 'No message found at that position'
                ]);
            }
            
            // Start mining the target message content
            return response()->json([
                'success' => true,
                'action' => 'start_mining',
                'target' => [
                    'type' => 'message',
                    'id' => $targetMessage->id,
                    'content' => substr($targetMessage->message, 0, 50) . '...',
                    'author' => $targetMessage->username,
                    'positions_back' => $positionsBack
                ],
                'difficulty' => '21e8'
            ]);
        }
        
        // /help command
        if (preg_match('/^\/help$/', $command)) {
            return response()->json([
                'success' => true,
                'action' => 'system_message',
                'message' => '🤖 Available commands:<br>' .
                           '/join #roomname - Join a chat room<br>' .
                           '/hp - Show total site hashpower stats<br>' .
                           '/hp -u [username] - Show specific user\'s hashpower<br>' .
                           '/mine - Mine the last posted message<br>' .
                           '/mine^^ - Mine message 2 positions above<br>' .
                           '/me [action] - Perform an action<br>' .
                           '/help - Show this help'
            ]);
        }
        
        // /me command for actions
        if (preg_match('/^\/me (.+)$/', $command, $matches)) {
            $actionText = $matches[1];
            $displayName = $room->users()->find($user->id)->pivot->display_name ?? $user->username;
            
            return response()->json([
                'success' => true,
                'action' => 'action_message',
                'message' => '* ' . $displayName . ' ' . $actionText
            ]);
        }
        
        return response()->json([
            'success' => false,
            'error' => 'Unknown command: ' . $command
        ]);
    }

    /**
     * Calculate user rank based on PoW points
     */
    private function calculateRank(int $points): string
    {
        if ($points >= 50000) return 'Legend 🏆';
        if ($points >= 25000) return 'Master ⭐';
        if ($points >= 10000) return 'Expert 💎';
        if ($points >= 5000) return 'Advanced ⚡';
        if ($points >= 2500) return 'Skilled 🔥';
        if ($points >= 1000) return 'Intermediate 🎯';
        if ($points >= 500) return 'Novice 🌱';
        if ($points >= 100) return 'Beginner 🔨';
        return 'Newcomer 👋';
    }
}