<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class SubscriptionService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createStripeSubscription(User $user): array
    {
        $plan = SubscriptionPlan::where('name', 'Monthly Subscription')->first();
        
        if (!$plan) {
            throw new \Exception('Monthly subscription plan not found');
        }

        return DB::transaction(function () use ($user, $plan) {
            // Create or retrieve Stripe customer
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);
            
            // Create Stripe subscription with setup fee
            $stripeSubscription = $this->stripe->subscriptions->create([
                'customer' => $stripeCustomer->id,
                'items' => [
                    ['price' => $plan->stripe_price_id]
                ],
                'add_invoice_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product' => $plan->stripe_product_id,
                            'unit_amount' => $plan->setup_fee_usd * 100, // Setup fee in cents
                        ],
                    ],
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // Create local subscription record
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'auto_renew' => true,
                'stripe_subscription_id' => $stripeSubscription->id
            ]);

            return [
                'subscription' => $subscription,
                'stripe_subscription' => $stripeSubscription,
                'client_secret' => $stripeSubscription->latest_invoice->payment_intent->client_secret
            ];
        });
    }

    private function getOrCreateStripeCustomer(User $user): \Stripe\Customer
    {
        if ($user->stripe_customer_id) {
            try {
                return $this->stripe->customers->retrieve($user->stripe_customer_id);
            } catch (ApiErrorException $e) {
                // Customer doesn't exist, create new one
            }
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name ?? $user->email,
            'metadata' => [
                'user_id' => $user->id
            ]
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);
        
        return $customer;
    }

    public function activateSubscription(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->update(['status' => 'active']);
        });
    }

    public function hasActiveSubscription(User $user): bool
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function renewSubscription(User $user): Subscription
    {
        $currentSubscription = $user->currentSubscription;
        
        if (!$currentSubscription) {
            throw new \Exception('No active subscription found');
        }

        $plan = $currentSubscription->subscriptionPlan;
        
        return DB::transaction(function () use ($user, $currentSubscription, $plan) {
            $currentSubscription->update(['status' => 'expired']);
            
            return Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
                'starts_at' => $currentSubscription->expires_at,
                'expires_at' => $currentSubscription->expires_at->copy()->addMonth(),
                'auto_renew' => true
            ]);
        });
    }

    public function getSubscriptionStatus(User $user): array
    {
        $currentSubscription = $user->currentSubscription;
        
        return [
            'has_active_subscription' => $this->hasActiveSubscription($user),
            'current_subscription' => $currentSubscription,
            'expires_at' => $currentSubscription?->expires_at,
            'needs_renewal' => $currentSubscription && $currentSubscription->expires_at->isPast(),
            'in_first_month_free' => $currentSubscription && 
                $currentSubscription->starts_at->diffInDays(now()) <= 31 &&
                $currentSubscription->subscriptionPlan->name === 'Signup with First Month Free'
        ];
    }

    public function getRequiredPayments(User $user): array
    {
        $payments = [];
        
        if (!$this->hasActiveSubscription($user)) {
            $signupPlan = SubscriptionPlan::where('name', 'Signup with First Month Free')->first();
            
            $currentSubscription = $user->currentSubscription;
            if (!$currentSubscription) {
                $payments[] = [
                    'type' => 'signup',
                    'amount' => $signupPlan->price_usd,
                    'description' => '$50 signup fee (includes first month free)'
                ];
            } else {
                $payments[] = [
                    'type' => 'renewal',
                    'amount' => 10.00,
                    'description' => 'Monthly subscription renewal ($10/month)'
                ];
            }
        }
        
        return $payments;
    }
}