<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\BitcoinAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    public function index()
    {
        $userId = session('bitcoin_auth_id');
        $user = $userId ? BitcoinAuth::find($userId) : null;

        $items = ShopItem::where('is_active', true)
            ->orderBy('type')
            ->orderBy('price')
            ->get();

        // Get user's purchases if logged in
        $userPurchases = [];
        if ($user) {
            $userPurchases = ShopPurchase::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('shop_item_id')
                ->toArray();
        }

        return view('shop.index', [
            'user' => $user,
            'items' => $items,
            'shopItems' => $this->formatItemsForView($items),
            'userPurchases' => $userPurchases,
        ]);
    }

    public function purchase(Request $request, $itemId)
    {
        $userId = session('bitcoin_auth_id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to make purchases',
            ], 401);
        }

        $user = BitcoinAuth::find($userId);
        $item = ShopItem::find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        // Check if user can purchase
        if (!$item->canBePurchasedBy($user)) {
            $reason = 'Cannot purchase this item';
            if (!$item->isAvailable()) {
                $reason = 'Item is not available';
            } elseif ($user->level < $item->level_required) {
                $reason = "Requires level {$item->level_required}";
            } elseif ($user->total_pow_points < $item->price) {
                $reason = 'Not enough points';
            }

            return response()->json([
                'success' => false,
                'message' => $reason,
            ], 400);
        }

        // Check if already purchased (for non-consumable items)
        $existingPurchase = ShopPurchase::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->where('is_active', true)
            ->first();

        if ($existingPurchase && !$this->isConsumable($item->type)) {
            return response()->json([
                'success' => false,
                'message' => 'You already own this item',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Deduct points
            $user->total_pow_points -= $item->price;
            $user->save();

            // Create purchase record
            $purchase = ShopPurchase::create([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
                'price_paid' => $item->price,
                'is_active' => true,
                'expires_at' => $this->calculateExpiration($item),
            ]);

            // Update stock if limited
            if ($item->stock !== null) {
                $item->stock--;
                $item->save();
            }

            // Apply item effects
            $this->applyItemEffects($user, $item);

            DB::commit();

            Log::info('Shop purchase', [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'price' => $item->price,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Purchased {$item->name}!",
                'remaining_points' => $user->fresh()->total_pow_points,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shop purchase failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Purchase failed. Please try again.',
            ], 500);
        }
    }

    private function isConsumable($type)
    {
        return in_array($type, ['boost', 'feature']);
    }

    private function calculateExpiration($item)
    {
        if (!isset($item->metadata['duration_days'])) {
            return null;
        }

        return now()->addDays($item->metadata['duration_days']);
    }

    private function applyItemEffects($user, $item)
    {
        // Apply item-specific effects based on type
        switch ($item->type) {
            case 'boost':
                if (isset($item->metadata['mining_power_boost'])) {
                    $user->mining_power += $item->metadata['mining_power_boost'];
                    $user->save();
                }
                break;
            // Add more item type handlers as needed
        }
    }
    
    private function formatItemsForView($items)
    {
        $formatted = [];
        
        foreach ($items as $item) {
            $formatted[$item->id] = [
                'name' => $item->name,
                'description' => $item->description,
                'cost' => $item->price,
                'icon' => $item->icon ?? '🛒',
                'category' => $item->category ?? 'utility',
                'type' => $item->type,
                'available' => $item->isAvailable(),
                'level_required' => $item->level_required ?? 0,
            ];
        }
        
        return $formatted;
    }
}
