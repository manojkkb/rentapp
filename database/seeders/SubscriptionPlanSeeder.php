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

            // 🔹 SILVER (free trial is automatic from vendor created_at — not a paid plan)
            [
                'name' => 'Silver Monthly',
                'slug' => 'silver-monthly',
                'type' => 'silver',
                'billing_cycle' => 'monthly',
                'price' => 199,
                'duration_days' => 30,
                'is_trial' => false,
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
                'is_trial' => false,
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
                'is_trial' => false,
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

        SubscriptionPlan::query()
            ->where('slug', 'trail-plan')
            ->update(['is_active' => false, 'is_trial' => true]);
    }
}
