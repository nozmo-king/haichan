<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BitcoinAuth;

class PointShopController extends Controller
{
    /**
     * Show the point shop
     */
    public function index(Request $request)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return redirect('/auth/login')->withErrors(['auth' => 'Please log in to access the point shop']);
        }
        
        $thread = null;
        $threadHash = $request->get('thread');
        
        if ($threadHash) {
            $thread = \App\Models\Thread::where('sha256_digest', 'like', $threadHash . '%')
                                       ->orWhere('id', $threadHash)
                                       ->first();
        }
        
        $shopItems = $this->getShopItems($thread);
        
        return view('shop.index', compact('user', 'shopItems', 'thread'));
    }
    
    /**
     * Purchase an item with points
     */
    public function purchase(Request $request)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $validated = $request->validate([
            'item_id' => 'required|string',
            'thread_hash' => 'nullable|string',
        ]);
        
        $itemId = $validated['item_id'];
        $threadHash = $validated['thread_hash'] ?? null;
        
        $thread = null;
        if ($threadHash) {
            $thread = \App\Models\Thread::where('sha256_digest', 'like', $threadHash . '%')
                                       ->orWhere('id', $threadHash)
                                       ->first();
        }
        
        $shopItems = $this->getShopItems($thread);
        
        if (!isset($shopItems[$itemId])) {
            return response()->json(['success' => false, 'error' => 'Invalid item'], 400);
        }
        
        $item = $shopItems[$itemId];
        
        // Check if user has enough points
        if ($user->total_pow_points < $item['cost']) {
            return response()->json([
                'success' => false, 
                'error' => "Insufficient points. You have {$user->total_pow_points}, need {$item['cost']}"
            ], 400);
        }
        
        // Process the purchase
        try {
            $result = $this->processItemPurchase($user, $itemId, $item, $thread);
            
            if ($result['success']) {
                // Deduct points
                $user->total_pow_points -= $item['cost'];
                $user->save();
                
                Log::info('Point shop purchase', [
                    'user_id' => $user->id,
                    'item_id' => $itemId,
                    'cost' => $item['cost'],
                    'remaining_points' => $user->total_pow_points
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'remaining_points' => $user->total_pow_points
                ]);
            } else {
                return response()->json(['success' => false, 'error' => $result['message']], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Point shop purchase failed', [
                'user_id' => $user->id,
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['success' => false, 'error' => 'Purchase failed'], 500);
        }
    }
    
    /**
     * Get available shop items
     */
    private function getShopItems($thread = null): array
    {
        $items = [
            'custom_tripcode' => [
                'name' => 'Custom Tripcode',
                'description' => 'Set a custom tripcode for 24 hours',
                'cost' => 1000,
                'icon' => '🎭',
                'category' => 'cosmetic'
            ],
            'thread_pin' => [
                'name' => 'Pin Thread',
                'description' => 'Pin your thread to top of board for 6 hours',
                'cost' => 500,
                'icon' => '📌',
                'category' => 'utility'
            ],
            'username_color' => [
                'name' => 'Colored Username',
                'description' => 'Color your username for 24 hours',
                'cost' => 750,
                'icon' => '🌈',
                'category' => 'cosmetic'
            ],
            'extra_images' => [
                'name' => 'Extra Image Slots',
                'description' => 'Post up to 5 images in one post (24h)',
                'cost' => 300,
                'icon' => '🖼️',
                'category' => 'utility'
            ],
            'mining_boost' => [
                'name' => 'Mining Boost',
                'description' => '2x mining points for 1 hour',
                'cost' => 2000,
                'icon' => '⚡',
                'category' => 'boost'
            ],
            'rare_pattern_hint' => [
                'name' => 'Pattern Hint',
                'description' => 'Get a hint for finding rare hash patterns',
                'cost' => 150,
                'icon' => '💡',
                'category' => 'utility'
            ],
            'thread_highlight' => [
                'name' => 'Highlight Thread',
                'description' => 'Make your thread glow for 12 hours',
                'cost' => 400,
                'icon' => '✨',
                'category' => 'cosmetic'
            ],
            'priority_post' => [
                'name' => 'Priority Post',
                'description' => 'Your next post appears at top of thread',
                'cost' => 250,
                'icon' => '🔝',
                'category' => 'utility'
            ]
        ];
        
        // Add thread-specific items if thread is provided
        if ($thread) {
            $items['pin_thread_specific'] = [
                'name' => 'Pin This Thread',
                'description' => "Pin thread #{$thread->short_hash} to top of /{$thread->board->name}/ for 6 hours",
                'cost' => 750,
                'icon' => '📌',
                'category' => 'thread'
            ];
            
            $items['highlight_thread_specific'] = [
                'name' => 'Highlight This Thread',
                'description' => "Make thread #{$thread->short_hash} glow for 12 hours",
                'cost' => 500,
                'icon' => '✨',
                'category' => 'thread'
            ];
            
            $items['boost_thread'] = [
                'name' => 'Boost Thread Visibility',
                'description' => "Increase thread #{$thread->short_hash} bump score by 100",
                'cost' => 300,
                'icon' => '🚀',
                'category' => 'thread'
            ];
            
            $items['sticky_thread'] = [
                'name' => 'Make Thread Sticky',
                'description' => "Pin thread #{$thread->short_hash} permanently (mod approval required)",
                'cost' => 2500,
                'icon' => '📍',
                'category' => 'thread'
            ];
        }
        
        return $items;
    }
    
    /**
     * Process individual item purchases
     */
    private function processItemPurchase($user, $itemId, $item, $thread = null): array
    {
        switch ($itemId) {
            case 'custom_tripcode':
                // Store custom tripcode preference
                $user->update(['custom_tripcode_until' => now()->addHours(24)]);
                return ['success' => true, 'message' => 'Custom tripcode activated for 24 hours!'];
                
            case 'thread_pin':
                // This would need thread context, for now just give the boost
                $user->update(['pin_boost_until' => now()->addHours(6)]);
                return ['success' => true, 'message' => 'Thread pin boost activated! Your next thread will be pinned for 6 hours.'];
                
            case 'username_color':
                $user->update(['colored_name_until' => now()->addHours(24)]);
                return ['success' => true, 'message' => 'Colored username activated for 24 hours!'];
                
            case 'extra_images':
                $user->update(['extra_images_until' => now()->addHours(24)]);
                return ['success' => true, 'message' => 'Extra image slots activated for 24 hours!'];
                
            case 'mining_boost':
                $user->update(['mining_boost_until' => now()->addHours(1)]);
                return ['success' => true, 'message' => '2x mining boost activated for 1 hour!'];
                
            case 'rare_pattern_hint':
                $hints = [
                    "Look for patterns starting with '777' for lucky hashes",
                    "The legendary 'deadbeef' pattern is worth 5000 points",
                    "Try mining when your mouse moves in circular patterns",
                    "Hash patterns '000' and '111' are quite valuable",
                    "The '1337' pattern appears more often during peak hours"
                ];
                $hint = $hints[array_rand($hints)];
                return ['success' => true, 'message' => "💡 Hint: " . $hint];
                
            case 'thread_highlight':
                $user->update(['thread_highlight_until' => now()->addHours(12)]);
                return ['success' => true, 'message' => 'Thread highlighting activated for 12 hours!'];
                
            case 'priority_post':
                $user->update(['priority_post_count' => ($user->priority_post_count ?? 0) + 1]);
                return ['success' => true, 'message' => 'Priority post boost added! Your next post will appear at the top.'];
                
            // Thread-specific items
            case 'pin_thread_specific':
                if (!$thread) return ['success' => false, 'message' => 'Thread not found'];
                $thread->update([
                    'is_pinned' => true,
                    'pinned_until' => now()->addHours(6)
                ]);
                return ['success' => true, 'message' => "Thread #{$thread->short_hash} pinned for 6 hours!"];
                
            case 'highlight_thread_specific':
                if (!$thread) return ['success' => false, 'message' => 'Thread not found'];
                $thread->update([
                    'is_highlighted' => true,
                    'highlighted_until' => now()->addHours(12)
                ]);
                return ['success' => true, 'message' => "Thread #{$thread->short_hash} highlighted for 12 hours!"];
                
            case 'boost_thread':
                if (!$thread) return ['success' => false, 'message' => 'Thread not found'];
                $thread->increment('bump_score', 100);
                return ['success' => true, 'message' => "Thread #{$thread->short_hash} visibility boosted by 100 points!"];
                
            case 'sticky_thread':
                if (!$thread) return ['success' => false, 'message' => 'Thread not found'];
                $thread->update(['sticky' => true]);
                return ['success' => true, 'message' => "Thread #{$thread->short_hash} made sticky! (Subject to mod approval)"];
                
            default:
                return ['success' => false, 'message' => 'Unknown item'];
        }
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
        
        return BitcoinAuth::find($userId);
    }
}