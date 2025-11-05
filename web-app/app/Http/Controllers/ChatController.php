<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Services\ChallengeVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    protected $verifier;

    public function __construct(ChallengeVerifier $verifier)
    {
        $this->verifier = $verifier;
        
        // Auth is already applied in routes, no need to duplicate here
        // Disable CSRF verification for sendMessage API endpoint only
        $this->middleware('web')->except(['sendMessage']);
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
        
        Log::info('Chat room access attempt', [
            'room' => $room->slug,
            'user_id' => $user?->id,
            'session_auth_id' => session('bitcoin_auth_id'),
            'has_user' => (bool) $user
        ]);
        
        if (!$user) {
            Log::warning('Chat access denied - no auth', ['room' => $room->slug]);
            return redirect('/')->withErrors(['auth' => 'Please log in to access chat']);
        }
        
        // Join user to room if not already joined
        $this->joinUserToRoom($room, $user);
        
        // Get recent messages (simplified)
        $messages = $room->messages()->with('user')->latest()->take(50)->get()->reverse();
        
        Log::info('Chat room loaded', [
            'room' => $room->slug,
            'user' => $user->username,
            'message_count' => count($messages)
        ]);
        
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
        
        // Validate input with required POW fields
        $validated = $request->validate([
            'message' => 'required|string|max:500|min:1',
            'pow_nonce' => 'required|string',
            'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
            'pow_challenge_id' => 'required|string',
        ]);

        $message = trim($validated['message']);
        
        // Check for commands (skip PoW validation for commands)
        if (str_starts_with($message, '/')) {
            return $this->executeCommand($request, $room);
        }

        try {
            // Validate proof-of-work using the centralized challenge verifier
            $powValidation = $this->verifier->verifyChallenge(
                $validated['pow_challenge_id'],
                $validated['pow_nonce'],
                $validated['pow_hash']
            );

            if (!$powValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => $powValidation['error'] ?? 'Invalid proof of work'
                ], 400);
            }
            
            // Consume the challenge so it can't be reused
            $this->verifier->consumeChallenge($validated['pow_challenge_id']);
            
            // Determine pattern and points from hash
            $pattern = $this->getPatternFromHash($validated['pow_hash']);
            $points = $this->calculatePoints($validated['pow_hash']);
            
            // Get user's display name with 21e8 diamonds and tripcode
            $displayName = $room->getUserDisplayName($user);

            // Create the message with validated POW
            $message = ChatMessage::create([
                'chat_room_id' => $room->id,
                'user_id' => $user->id,
                'username' => $displayName,
                'message' => $validated['message'],
                'message_hash' => hash('sha256', $validated['message']),
                'ip_hash' => hash('sha256', $request->ip()),
                'pow_hash' => $validated['pow_hash'],
                'pow_nonce' => $validated['pow_nonce'],
                'pow_pattern' => $pattern,
                'pow_points' => $points,
                'pow_challenge_id' => $validated['pow_challenge_id'],
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
                // Get user's 21e8 diamond color
                $diamondColor = null;
                if ($message->user && $message->user->personal_21e8_hash) {
                    $hash = strtolower($message->user->personal_21e8_hash);
                    if (str_starts_with($hash, '21e80000')) {
                        $diamondColor = '#FF1493'; // Hot pink for 21e80000
                    } elseif (str_starts_with($hash, '21e8000')) {
                        $diamondColor = '#FF00FF'; // Magenta for 21e8000
                    } elseif (str_starts_with($hash, '21e800')) {
                        $diamondColor = '#9370DB'; // Purple for 21e800
                    } elseif (str_starts_with($hash, '21e80')) {
                        $diamondColor = '#4169E1'; // Royal blue for 21e80
                    } elseif (str_starts_with($hash, '21e8')) {
                        $diamondColor = '#00CED1'; // Dark turquoise for 21e8
                    }
                }
                
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'username' => $message->username ?? $message->user->username ?? $message->user->address ?? 'Anonymous',
                    'diamond_color' => $diamondColor,
                    'created_at' => $message->created_at->format('H:i:s'),
                    'hash_preview' => substr($message->pow_hash ?? '', 0, 8),
                    'points' => $this->calculatePointsFromHash($message->pow_hash ?? ''),
                    'pow_hash' => $message->pow_hash ?? '',
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
        // Ensure user exists before attempting to join
        if (!$user || !$user->id) {
            return;
        }

        try {
            // Check if user is already in room
            if (!$room->users()->where('user_id', $user->id)->exists()) {
                $room->users()->attach($user->id, [
                    'display_name' => $user->username ?? $user->address ?? 'Anonymous',
                    'joined_at' => now(),
                    'last_seen_at' => now(),
                    'total_messages' => 0,
                    'total_pow_points' => 0,
                    'is_muted' => false,
                    'permissions' => 'user',
                ]);
            } else {
                // Update last seen time for existing user
                $room->users()->updateExistingPivot($user->id, [
                    'last_seen_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to join user to chat room', [
                'user_id' => $user->id,
                'room_id' => $room->id,
                'error' => $e->getMessage()
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
        if (str_starts_with($hash, '21e8')) return 10;
        if (str_starts_with($hash, '21e')) return 5;
        if (str_starts_with($hash, '21')) return 2.5;
        if (str_starts_with($hash, '2')) return 1;
        return max(1, floor(64 - strlen(ltrim($hash, '0')) / 4)); // Real calculation based on leading zeros
    }

    /**
     * Calculate points from hash for display
     */
    private function calculatePointsFromHash(string $hash): int
    {
        if (empty($hash)) return 0;
        return $this->calculatePoints($hash);
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
        try {
            $user = $this->getBitcoinAuthUser();
            
            if (!$user) {
                Log::warning('Chat users request without auth', ['room' => $room->slug]);
                return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
            }
            
            Log::info('Getting online users', ['room' => $room->slug, 'user' => $user->username]);
            
            // Auto-join user to room if not already joined
            $this->joinUserToRoom($room, $user);
            
            $users = $room->users()
                         ->wherePivot('last_seen_at', '>', now()->subMinutes(15))
                         ->orderByPivot('last_seen_at', 'desc')
                         ->get()
                         ->map(function ($roomUser) {
                             return [
                                 'id' => $roomUser->id,
                                 'display_name' => $roomUser->pivot->display_name ?? ($roomUser->username . ' !' . substr(hash('sha256', $roomUser->address), 0, 6)),
                                 'username' => $roomUser->username ?? 'Anonymous',
                                 'last_seen' => $roomUser->pivot->last_seen_at ? $roomUser->pivot->last_seen_at->diffForHumans() : 'just now',
                             ];
                         });
            
            Log::info('Found users', ['count' => $users->count()]);
            
            return response()->json([
                'success' => true,
                'users' => $users,
                'debug' => [
                    'room_id' => $room->id,
                    'current_user' => $user->username,
                    'total_room_users' => $room->users()->count(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting online users', [
                'room' => $room->slug ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
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
        
        try {
            $user = \App\Models\BitcoinAuth::find($userId);
            
            // Verify user actually exists
            if (!$user || !$user->exists) {
                Log::warning('BitcoinAuth user not found', ['user_id' => $userId]);
                return null;
            }
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Error fetching BitcoinAuth user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
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
        
        // Handle both 'command' field (from JavaScript) and 'message' field (from sendMessage)
        $command = null;
        if ($request->has('command')) {
            $validated = $request->validate(['command' => 'required|string|max:500']);
            $command = trim($validated['command']);
        } elseif ($request->has('message')) {
            $validated = $request->validate(['message' => 'required|string|max:500']);
            $command = trim($validated['message']);
        } else {
            return response()->json(['success' => false, 'error' => 'No command provided'], 400);
        }
        
        // Parse /join #roomname command
        if (preg_match('/^\/join #(\w+)$/', $command, $matches)) {
            return $this->handleJoinCommand($matches[1], $user);
        }
        
        // Parse /create #roomname [description] command
        if (preg_match('/^\/create #(\w+)(?:\s+(.+))?$/', $command, $matches)) {
            return $this->handleCreateCommand($matches[1], $matches[2] ?? '', $user);
        }
        
        // Parse /register #roomname [min_points] command 
        if (preg_match('/^\/register #(\w+)(?:\s+(\d+))?$/', $command, $matches)) {
            return $this->handleRegisterCommand($matches[1], intval($matches[2] ?? 100), $user);
        }
        
        // Parse /list command
        if (preg_match('/^\/list$/', $command)) {
            return $this->handleListCommand();
        }
        
        // Parse /msg nameserv register username command
        if (preg_match('/^\/msg nameserv register (\w+)$/', $command, $matches)) {
            return $this->handleNameservRegister($matches[1], $user);
        }
        
        // Parse /msg nameserv identify username command
        if (preg_match('/^\/msg nameserv identify (\w+)$/', $command, $matches)) {
            return $this->handleNameservIdentify($matches[1], $user);
        }
        
        // Parse /mine #roomname command
        if (preg_match('/^\/mine #(\w+)$/', $command, $matches)) {
            return $this->handleMineChannel($matches[1], $user);
        }
        
        // /hp command (show total site hashpower)
        if (preg_match('/^\/hp$/', $command)) {
            $totalSitePoints = \App\Models\BitcoinAuth::sum('total_pow_points') ?? 0;
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
            
            $targetPoints = $targetUser->total_pow_points ?? 0;
            $targetRank = $this->calculateRank($targetPoints);
            $userPoints = $user->total_pow_points ?? 0;
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
    
    /**
     * Handle /join #roomname command
     */
    private function handleJoinCommand(string $roomSlug, $user)
    {
        // Special handling for secret admin room
        if ($roomSlug === 'sadmin') {
            $adminRoom = ChatRoom::where('slug', 'sadmin')->first();
            
            if ($adminRoom && $user->total_pow_points >= 1000) {
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
        
        // Find or create room
        $targetRoom = ChatRoom::where('slug', $roomSlug)->first();
        
        if (!$targetRoom) {
            // Auto-create public rooms when joined
            $targetRoom = ChatRoom::create([
                'slug' => $roomSlug,
                'name' => ucfirst($roomSlug),
                'description' => 'Auto-created channel',
                'pow_difficulty' => '21e8',
                'min_pow_points' => 0,
                'is_active' => true,
                'is_public' => true,
                'max_users' => 100,
                'message_rate_limit' => 60,
                'owner_id' => null // No owner for auto-created rooms
            ]);
        }
        
        // Check if user meets requirements
        if ($targetRoom->min_pow_points > 0 && $user->total_pow_points < $targetRoom->min_pow_points) {
            return response()->json([
                'success' => false,
                'error' => "Channel #{$roomSlug} requires {$targetRoom->min_pow_points} PoW points. You have {$user->total_pow_points}."
            ]);
        }
        
        $this->joinUserToRoom($targetRoom, $user);
        return response()->json([
            'success' => true,
            'action' => 'redirect',
            'url' => route('chat.room', ['room' => $targetRoom])
        ]);
    }
    
    /**
     * Handle /create #roomname [description] command
     */
    private function handleCreateCommand(string $roomSlug, string $description, $user)
    {
        // Check if room already exists
        if (ChatRoom::where('slug', $roomSlug)->exists()) {
            return response()->json([
                'success' => false,
                'error' => "Channel #{$roomSlug} already exists"
            ]);
        }
        
        // Create new room
        $room = ChatRoom::create([
            'slug' => $roomSlug,
            'name' => ucfirst($roomSlug),
            'description' => $description ?: "Created by {$user->username}",
            'pow_difficulty' => '21e8',
            'min_pow_points' => 0,
            'is_active' => true,
            'is_public' => true,
            'max_users' => 100,
            'message_rate_limit' => 60,
            'owner_id' => $user->id
        ]);
        
        $this->joinUserToRoom($room, $user);
        
        return response()->json([
            'success' => true,
            'action' => 'system_message',
            'message' => "✅ Channel #{$roomSlug} created successfully! You are now the owner.",
            'redirect_url' => route('chat.room', ['room' => $room])
        ]);
    }
    
    /**
     * Handle /register #roomname [min_points] command
     */
    private function handleRegisterCommand(string $roomSlug, int $minPoints, $user)
    {
        $room = ChatRoom::where('slug', $roomSlug)->first();
        
        if (!$room) {
            return response()->json([
                'success' => false,
                'error' => "Channel #{$roomSlug} does not exist"
            ]);
        }
        
        // Only owners or users with 1000+ points can register channels
        if ($room->owner_id !== $user->id && $user->total_pow_points < 1000) {
            return response()->json([
                'success' => false,
                'error' => 'Only channel owners or users with 1000+ PoW points can register channels'
            ]);
        }
        
        // Update room registration
        $room->update([
            'owner_id' => $user->id,
            'min_pow_points' => $minPoints,
            'is_registered' => true,
            'registered_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'action' => 'system_message',
            'message' => "✅ Channel #{$roomSlug} registered successfully! Min PoW points: {$minPoints}"
        ]);
    }
    
    /**
     * Handle /list command
     */
    private function handleListCommand()
    {
        $rooms = ChatRoom::where('is_active', true)
            ->where('is_public', true)
            ->withCount('users')
            ->orderBy('users_count', 'desc')
            ->take(20)
            ->get();
        
        $list = $rooms->map(function($room) {
            $status = $room->is_registered ? '🔒' : '🌐';
            $users = $room->users_count;
            $points = $room->min_pow_points > 0 ? " ({$room->min_pow_points}+ pts)" : '';
            return "#{$room->slug} [{$users}]{$points} {$status} {$room->description}";
        });
        
        $message = "📋 **Public Channels:**\n" . $list->join("\n");
        if ($list->isEmpty()) {
            $message = "📋 No public channels found. Use /create #channelname to create one!";
        }
        
        return response()->json([
            'success' => true,
            'action' => 'system_message',
            'message' => $message
        ]);
    }
    
    /**
     * Handle nameserv nickname registration
     */
    private function handleNameservRegister(string $nickname, $user)
    {
        // Check if nickname is already taken
        $existing = \App\Models\BitcoinAuth::where('username', $nickname)->first();
        if ($existing && $existing->id !== $user->id) {
            return response()->json([
                'success' => true,
                'action' => 'system_message', 
                'message' => "🤖 <NameServ> Nickname '{$nickname}' is already registered to another user."
            ]);
        }
        
        // Register/update nickname
        $user->update(['username' => $nickname]);
        
        return response()->json([
            'success' => true,
            'action' => 'system_message',
            'message' => "🤖 <NameServ> Nickname '{$nickname}' has been registered to your Bitcoin address. Your tripcode is !" . substr(hash('sha256', $user->address), 0, 6)
        ]);
    }
    
    /**
     * Handle nameserv nickname identification
     */
    private function handleNameservIdentify(string $nickname, $user)
    {
        if ($user->username === $nickname) {
            return response()->json([
                'success' => true,
                'action' => 'system_message',
                'message' => "🤖 <NameServ> You are now identified as {$nickname}. Tripcode: !" . substr(hash('sha256', $user->address), 0, 6)
            ]);
        } else {
            return response()->json([
                'success' => true,
                'action' => 'system_message',
                'message' => "🤖 <NameServ> Invalid identification for '{$nickname}'. Use your registered nickname."
            ]);
        }
    }
    
    /**
     * Handle channel mining
     */
    private function handleMineChannel(string $roomSlug, $user)
    {
        $room = ChatRoom::where('slug', $roomSlug)->first();
        
        if (!$room) {
            return response()->json([
                'success' => false,
                'error' => "Channel #{$roomSlug} does not exist"
            ]);
        }
        
        // Start mining the channel name
        return response()->json([
            'success' => true,
            'action' => 'start_mining',
            'target' => [
                'type' => 'channel',
                'slug' => $roomSlug,
                'name' => $room->name,
                'difficulty' => $room->pow_difficulty,
                'reward' => 'Channel ownership/points'
            ],
            'difficulty' => $room->pow_difficulty,
            'message' => "⛏️ Mining channel #{$roomSlug} with difficulty {$room->pow_difficulty}..."
        ]);
    }
}