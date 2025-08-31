<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class SubscriptionController extends Controller
{
    public function plans(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        $user = $request->user();
        $currentSubscription = $user?->currentSubscription;

        return view('subscriptions.plans', compact('plans', 'currentSubscription'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();

        if ($user->hasActiveSubscription()) {
            return redirect()->back()->with('error', 'You already have an active subscription.');
        }

        if ($plan->needsStripeSetup()) {
            return redirect()->back()->with('error', 'This subscription plan is not yet configured. Please contact support.');
        }

        try {
            $stripeSecret = config('services.stripe.secret');
            
            if (empty($stripeSecret)) {
                throw new \Exception('Stripe secret key is not configured');
            }
            
            $stripe = new StripeClient($stripeSecret);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
                'starts_at' => now(),
                'expires_at' => now()->addMonths($plan->duration_months),
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount_usd' => $plan->price_usd,
                'payment_gateway' => 'stripe',
                'expires_at' => now()->addHours(2),
            ]);

            // Determine checkout mode based on plan type
            $mode = ($plan->duration_months == 999 || $plan->name === 'Signup with First Month Free') ? 'payment' : 'subscription';
            
            $sessionParams = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ]],
                'mode' => $mode,
                'success_url' => route('payment.success', ['payment' => $payment->id]),
                'cancel_url' => route('payment.cancel', ['payment' => $payment->id]),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                ],
            ];

            // For subscription mode, add customer email if available
            if ($mode === 'subscription' && !empty($user->email)) {
                $sessionParams['customer_email'] = $user->email;
            }

            $session = $stripe->checkout->sessions->create($sessionParams);

            $payment->update([
                'stripe_checkout_session_id' => $session->id,
                'gateway_payment_id' => $session->id,
            ]);

            return redirect($session->url);

        } catch (ApiErrorException $e) {
            return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $currentSubscription = $user->currentSubscription;
        $subscriptionHistory = $user->subscriptions()->with('subscriptionPlan')->latest()->paginate(10);
        $paymentHistory = $user->payments()->latest()->paginate(10);

        return view('subscriptions.dashboard', compact(
            'currentSubscription',
            'subscriptionHistory', 
            'paymentHistory'
        ));
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== $request->user()->id) {
            abort(403);
        }

        $subscription->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Subscription cancelled successfully.');
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->currentSubscription;

        return response()->json([
            'has_active_subscription' => $user->hasActiveSubscription(),
            'subscription' => $subscription ? [
                'plan' => $subscription->subscriptionPlan->name,
                'expires_at' => $subscription->expires_at->toISOString(),
                'auto_renew' => $subscription->auto_renew,
            ] : null,
        ]);
    }

    private function calculateCryptoAmount(float $usdAmount, string $cryptocurrency): float
    {
        $rates = [
            'BTC' => 0.000025,
            'ETH' => 0.0045,
            'LTC' => 0.15,
        ];

        return $usdAmount * ($rates[$cryptocurrency] ?? 1);
    }

    private function generateWalletAddress(string $cryptocurrency): string
    {
        $prefixes = [
            'BTC' => 'bc1',
            'ETH' => '0x',
            'LTC' => 'ltc1',
        ];

        $prefix = $prefixes[$cryptocurrency] ?? '';
        $address = $prefix . bin2hex(random_bytes(16));

        return $address;
    }
}
