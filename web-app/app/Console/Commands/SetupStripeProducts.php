<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionPlan;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class SetupStripeProducts extends Command
{
    protected $signature = 'stripe:setup-products';
    protected $description = 'Create Stripe products and prices for subscription plans';

    public function handle()
    {
        $stripeSecret = config('services.stripe.secret');
        
        if (empty($stripeSecret)) {
            $this->error('Stripe secret key is not configured');
            return 1;
        }

        $stripe = new StripeClient($stripeSecret);

        $plans = SubscriptionPlan::whereNull('stripe_product_id')
            ->orWhereNull('stripe_price_id')
            ->get();

        if ($plans->isEmpty()) {
            $this->info('All subscription plans already have Stripe products configured.');
            return 0;
        }

        foreach ($plans as $plan) {
            $this->info("Setting up Stripe product for: {$plan->name}");

            try {
                $product = null;
                $price = null;

                // Create or retrieve product
                if (empty($plan->stripe_product_id)) {
                    $product = $stripe->products->create([
                        'name' => $plan->name,
                        'description' => "Subscription plan: {$plan->name}",
                        'metadata' => [
                            'plan_id' => $plan->id,
                            'duration_months' => $plan->duration_months,
                        ]
                    ]);
                    $this->info("Created Stripe product: {$product->id}");
                } else {
                    $product = $stripe->products->retrieve($plan->stripe_product_id);
                    $this->info("Using existing Stripe product: {$product->id}");
                }

                // Create price if needed
                if (empty($plan->stripe_price_id)) {
                    // Determine if this is a one-time payment or recurring
                    $isRecurring = !str_contains(strtolower($plan->name), 'signup');
                    
                    $priceData = [
                        'product' => $product->id,
                        'unit_amount' => (int)($plan->price_usd * 100), // Convert to cents
                        'currency' => 'usd',
                        'metadata' => [
                            'plan_id' => $plan->id,
                        ]
                    ];

                    if ($isRecurring) {
                        $priceData['recurring'] = [
                            'interval' => 'month',
                            'interval_count' => $plan->duration_months,
                        ];
                    }

                    $price = $stripe->prices->create($priceData);
                    $this->info("Created Stripe price: {$price->id} (" . ($isRecurring ? 'recurring' : 'one-time') . ")");
                }

                // Update local database
                $updateData = [];
                if ($product) {
                    $updateData['stripe_product_id'] = $product->id;
                }
                if ($price) {
                    $updateData['stripe_price_id'] = $price->id;
                }

                if (!empty($updateData)) {
                    $plan->update($updateData);
                    $this->info("Updated local plan with Stripe IDs");
                }

                $this->line('---');

            } catch (ApiErrorException $e) {
                $this->error("Stripe API error for {$plan->name}: " . $e->getMessage());
            } catch (\Exception $e) {
                $this->error("Error setting up {$plan->name}: " . $e->getMessage());
            }
        }

        $this->info('Stripe products setup complete!');
        return 0;
    }
}