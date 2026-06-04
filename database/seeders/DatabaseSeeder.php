<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            LanguageSeeder::class,
            BusinessCategorySeeder::class,
            VendorSeeder::class,
            CategorySeeder::class,
            ItemSeeder::class,
            CouponSeeder::class,
            SubscriptionPlanSeeder::class,
            PlatformSettingsSeeder::class,
        ]);
    }
}
