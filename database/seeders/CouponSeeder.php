<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::first();

        if (!$vendor) {
            $this->command->warn('⚠️ No vendor found. Run VendorSeeder first.');
            return;
        }

        if (Coupon::where('vendor_id', $vendor->id)->exists()) {
            $this->command->info('✅ Coupons already exist for this vendor!');
            return;
        }

        $coupons = [
            [
                'vendor_id' => $vendor->id,
                'code' => 'WELCOME10',
                'name' => 'Welcome 10% Off',
                'type' => 'percent',
                'value' => 10.00,
                'min_order_amount' => 500.00,
                'max_discount_amount' => 200.00,
                'usage_limit' => 100,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor->id,
                'code' => 'FLAT50',
                'name' => 'Flat ₹50 Off',
                'type' => 'fixed',
                'value' => 50.00,
                'min_order_amount' => 300.00,
                'max_discount_amount' => null,
                'usage_limit' => 200,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor->id,
                'code' => 'SUMMER25',
                'name' => 'Summer Sale 25% Off',
                'type' => 'percent',
                'value' => 25.00,
                'min_order_amount' => 1000.00,
                'max_discount_amount' => 500.00,
                'usage_limit' => 50,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor->id,
                'code' => 'FLAT100',
                'name' => 'Flat ₹100 Off',
                'type' => 'fixed',
                'value' => 100.00,
                'min_order_amount' => 750.00,
                'max_discount_amount' => null,
                'usage_limit' => 75,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(4),
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor->id,
                'code' => 'EXPIRED20',
                'name' => 'Expired Coupon',
                'type' => 'percent',
                'value' => 20.00,
                'min_order_amount' => 0.00,
                'max_discount_amount' => null,
                'usage_limit' => 10,
                'used_count' => 10,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->subMonth(),
                'is_active' => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }

        $this->command->info('✅ 5 coupons seeded for vendor: ' . $vendor->name);
    }
}
