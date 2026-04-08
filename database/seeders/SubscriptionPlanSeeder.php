<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $plans = [

            // 🔹 SILVER
            [
                'name' => 'Trail Plan',
                'slug' => 'trail-plan',
                'type' => 'silver',
                'billing_cycle' => 'monthly',
                'price' => 199,
                'duration_days' => 30,
                'features' => [
                    'max_listings' => 10,
                    'max_users' => 2,
                    'priority_support' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Silver Yearly',
                'slug' => 'silver-yearly',
                'type' => 'silver',
                'billing_cycle' => 'yearly',
                'price' => 1999,
                'duration_days' => 365,
                'features' => [
                    'max_listings' => 10,
                    'max_users' => 2,
                    'priority_support' => false,
                ],
                'is_active' => true,
            ],

            // 🔹 GOLD
            [
                'name' => 'Gold Monthly',
                'slug' => 'gold-monthly',
                'type' => 'gold',
                'billing_cycle' => 'monthly',
                'price' => 499,
                'duration_days' => 30,
                'features' => [
                    'max_listings' => 50,
                    'max_users' => 5,
                    'priority_support' => true,
                    'advanced_reports' => true,
                ],
                'is_active' => true,
                'is_popular' => true,
            ],
          

        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
