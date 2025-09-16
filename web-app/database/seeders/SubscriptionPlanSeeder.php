<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Monthly Subscription',
                'duration_months' => 1,
                'price_usd' => 10.00,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'features' => [
                    'Full forum access',
                    'All features',
                    'Community support',
                    'Monthly billing'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Signup with First Month Free',
                'duration_months' => 1,
                'price_usd' => 50.00,
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'features' => [
                    'Full forum access',
                    'First month included',
                    'Community support',
                    'One-time signup fee'
                ],
                'is_active' => true
            ]
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
