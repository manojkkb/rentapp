<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test vendor user
        $user = User::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'mobile' => '9876543210',
            'language' => 'en',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create the vendor profile
        Vendor::create([
            'user_id' => $user->id,
            'owner_name' => $user->name,
            'language' => $user->language,
            'name' => 'Test Store',
            'slug' => Str::slug('Test Store'),
            'address_line1' => '123 Main Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'country' => 'India',
            'is_verified' => true,
            'is_active' => true,
            'rating' => 4.5,
            'total_reviews' => 10,
        ]);
        
        $this->command->info('✅ Test vendor created successfully!');
        $this->command->info('📱 Mobile: 9876543210');
        $this->command->info('🔑 Password: password123');
    }
}
