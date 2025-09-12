<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\User;
use App\Models\AllowedPublicKey;
use Carbon\Carbon;

class SubscriptionApiController extends Controller
{
    public function getStatus(Request $request)
    {
        $user = $request->user();
        $currentSubscription = $user->currentSubscription;
        
        if (!$currentSubscription) {
            return response()->json([
                'is_subscribed' => false,
                'subscription_type' => null,
                'expires_at' => null
            ]);
        }
        
        return response()->json([
            'is_subscribed' => $currentSubscription->isActive(),
            'subscription_type' => $currentSubscription->subscriptionPlan->name ?? 'premium',
            'expires_at' => $currentSubscription->expires_at ? $currentSubscription->expires_at->toISOString() : null
        ]);
    }
    
    public function activate(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|in:ios,android,web',
            'receipt_data' => 'nullable|string',
            'test_mode' => 'boolean'
        ]);
        
        $user = $request->user();
        $isTestMode = $request->boolean('test_mode', false);
        
        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an active subscription',
                'subscription' => null
            ], 400);
        }
        
        try {
            // For test mode, create a subscription without payment processing
            if ($isTestMode) {
                $subscription = $this->createTestSubscription($user, $request->platform);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Test subscription activated successfully',
                    'subscription' => [
                        'is_subscribed' => true,
                        'subscription_type' => $subscription->subscriptionPlan->name ?? 'premium',
                        'expires_at' => $subscription->expires_at ? $subscription->expires_at->toISOString() : null
                    ]
                ]);
            }
            
            // Production mode: validate the receipt
            if ($request->platform === 'ios') {
                $validation = $this->validateAppleReceipt($request->receipt_data);
                
                if (!$validation['valid']) {
                    $statusCode = isset($validation['existing_subscription']) ? 409 : 400;
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid receipt: ' . $validation['error'],
                        'subscription' => null
                    ], $statusCode);
                }
                
                $subscription = $this->createSubscriptionFromAppleReceipt(
                    $user, 
                    $validation['receipt'], 
                    $validation['subscription_info']
                );
            } else {
                // For non-iOS platforms, fall back to test mode for now
                $subscription = $this->createTestSubscription($user, $request->platform);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => [
                    'is_subscribed' => true,
                    'subscription_type' => $subscription->subscriptionPlan->name ?? 'premium',
                    'expires_at' => $subscription->expires_at ? $subscription->expires_at->toISOString() : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate subscription: ' . $e->getMessage(),
                'subscription' => null
            ], 500);
        }
    }
    
    public function cancel(Request $request)
    {
        $user = $request->user();
        $currentSubscription = $user->currentSubscription;
        
        if (!$currentSubscription || !$currentSubscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
                'subscription' => null
            ], 400);
        }
        
        try {
            // Mark subscription as cancelled but don't delete it
            $currentSubscription->update([
                'status' => 'cancelled',
                'expires_at' => now() // Immediate cancellation for test mode
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'subscription' => [
                    'is_subscribed' => false,
                    'subscription_type' => null,
                    'expires_at' => null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
                'subscription' => null
            ], 500);
        }
    }
    
    private function createTestSubscription($user, $platform)
    {
        // Find or create a default subscription plan
        $defaultPlan = \App\Models\SubscriptionPlan::firstOrCreate([
            'name' => 'Premium Monthly'
        ], [
            'duration_months' => 1,
            'price_usd' => 4.99, // Updated to match the €4.99 price point
            'stripe_price_id' => null,
            'stripe_product_id' => null,
            'features' => ['forum_access', 'mining', 'no_ads'],
            'is_active' => true
        ]);
        
        return Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $defaultPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(), // 1 month subscription for testing
            'auto_renew' => true
        ]);
    }
    
    private function validateAppleReceipt($receiptData)
    {
        if (empty($receiptData)) {
            return ['valid' => false, 'error' => 'Receipt data is required'];
        }
        
        // Apple's App Store receipt validation endpoint
        $productionUrl = 'https://buy.itunes.apple.com/verifyReceipt';
        $sandboxUrl = 'https://sandbox.itunes.apple.com/verifyReceipt';
        
        $requestData = [
            'receipt-data' => $receiptData,
            'password' => config('services.apple.shared_secret'), // Add this to your config
            'exclude-old-transactions' => true
        ];
        
        // Try production first
        $response = $this->sendReceiptValidationRequest($productionUrl, $requestData);
        
        // If production fails with sandbox receipt, try sandbox
        if (isset($response['status']) && $response['status'] === 21007) {
            $response = $this->sendReceiptValidationRequest($sandboxUrl, $requestData);
        }
        
        if (!isset($response['status'])) {
            return ['valid' => false, 'error' => 'Invalid response from Apple'];
        }
        
        if ($response['status'] !== 0) {
            return ['valid' => false, 'error' => 'Apple validation failed with status: ' . $response['status']];
        }
        
        // Verify the subscription is for our app and product
        $receipt = $response['receipt'];
        $bundleId = config('app.ios_bundle_id', 'com.haichan.app'); // Add this to your config
        
        if ($receipt['bundle_id'] !== $bundleId) {
            return ['valid' => false, 'error' => 'Receipt is not for this app'];
        }
        
        // First, extract transaction info to get the original_transaction_id
        // which is unique per Apple user and prevents subscription abuse
        $originalTransactionId = null;
        $transactionId = null;
        
        if (isset($response['latest_receipt_info'])) {
            foreach ($response['latest_receipt_info'] as $transaction) {
                if ($transaction['product_id'] === 'haichan_premium_monthly') {
                    $originalTransactionId = $transaction['original_transaction_id'];
                    $transactionId = $transaction['transaction_id'];
                    break;
                }
            }
        }
        
        if (!$originalTransactionId) {
            return ['valid' => false, 'error' => 'No original transaction ID found in receipt'];
        }
        
        // Check if this original_transaction_id already has an active subscription
        // This prevents the same Apple purchase from being used for multiple keypairs
        $existingSubscription = Subscription::where('apple_original_transaction_id', $originalTransactionId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
            
        if ($existingSubscription) {
            return [
                'valid' => false, 
                'error' => 'This Apple subscription is already being used by another account',
                'existing_subscription' => true
            ];
        }
        
        // Generate a consistent user identifier from the original transaction
        // This groups all subscriptions from the same Apple user
        $appleUserId = hash('sha256', $originalTransactionId . $receipt['bundle_id']);
        
        // Validate the subscription expiry
        $expiresDate = null;
        if (isset($response['latest_receipt_info'])) {
            foreach ($response['latest_receipt_info'] as $transaction) {
                if ($transaction['product_id'] === 'haichan_premium_monthly') {
                    $expiresDate = isset($transaction['expires_date_ms']) 
                        ? Carbon::createFromTimestampMs($transaction['expires_date_ms'])
                        : null;
                    break;
                }
            }
        }
        
        if (!$expiresDate || $expiresDate->isPast()) {
            return ['valid' => false, 'error' => 'Subscription has expired'];
        }
        
        $hasValidSubscription = true;
        $subscriptionInfo = [
            'apple_user_id' => $appleUserId,
            'transaction_id' => $transactionId,
            'original_transaction_id' => $originalTransactionId,
            'expires_date' => $expiresDate
        ];
        
        if (!$hasValidSubscription) {
            return ['valid' => false, 'error' => 'No valid subscription found in receipt'];
        }
        
        return [
            'valid' => true, 
            'receipt' => $response,
            'subscription_info' => $subscriptionInfo
        ];
    }
    
    private function sendReceiptValidationRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("HTTP error: $httpCode");
        }
        
        return json_decode($response, true);
    }
    
    private function createSubscriptionFromAppleReceipt($user, $receiptResponse, $subscriptionInfo)
    {
        // Create or find the subscription plan
        $subscriptionPlan = \App\Models\SubscriptionPlan::firstOrCreate([
            'name' => 'Premium Monthly'
        ], [
            'duration_months' => 1,
            'price_usd' => 4.99,
            'stripe_price_id' => null,
            'stripe_product_id' => null,
            'features' => ['forum_access', 'mining', 'no_ads'],
            'is_active' => true
        ]);
        
        return Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $subscriptionInfo['expires_date'],
            'auto_renew' => true,
            'platform' => 'ios',
            'external_subscription_id' => 'haichan_premium_monthly',
            'apple_user_id' => $subscriptionInfo['apple_user_id'],
            'apple_transaction_id' => $subscriptionInfo['transaction_id'],
            'apple_original_transaction_id' => $subscriptionInfo['original_transaction_id']
        ]);
    }
    
    public function activateForPublicKey(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string',
            'platform' => 'required|string|in:ios,android,web',
            'receipt_data' => 'nullable|string',
            'test_mode' => 'boolean'
        ]);
        
        // First, register the public key if it doesn't exist (for new users with subscription)
        $allowedKey = AllowedPublicKey::where('public_key', $request->public_key)->first();
        
        if (!$allowedKey) {
            // Create new allowed public key for subscription activation
            $allowedKey = AllowedPublicKey::create([
                'public_key' => $request->public_key,
                'label' => 'User registered via iOS subscription',
                'is_active' => true
            ]);
        }
        
        // Now find or create user
        $user = User::findOrCreateForPublicKey($request->public_key);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user for public key',
                'subscription' => null
            ], 500);
        }
        
        $isTestMode = $request->boolean('test_mode', false);
        
        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an active subscription',
                'subscription' => null
            ], 400);
        }
        
        try {
            // For production iOS receipts, validate with Apple
            if ($request->platform === 'ios' && !$isTestMode) {
                $validation = $this->validateAppleReceipt($request->receipt_data);
                
                if (!$validation['valid']) {
                    $statusCode = isset($validation['existing_subscription']) ? 409 : 400;
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid receipt: ' . $validation['error'],
                        'subscription' => null
                    ], $statusCode);
                }
                
                $subscription = $this->createSubscriptionFromAppleReceipt(
                    $user, 
                    $validation['receipt'], 
                    $validation['subscription_info']
                );
            } else {
                // Test mode or non-iOS platforms
                $subscription = $this->createTestSubscription($user, $request->platform);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully',
                'subscription' => [
                    'is_subscribed' => true,
                    'subscription_type' => $subscription->subscriptionPlan->name ?? 'premium',
                    'expires_at' => $subscription->expires_at ? $subscription->expires_at->toISOString() : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate subscription: ' . $e->getMessage(),
                'subscription' => null
            ], 500);
        }
    }
}