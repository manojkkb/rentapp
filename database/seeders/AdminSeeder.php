<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@rentapp.com'],
            [
                'name' => 'Super Admin',
                'phone' => '+919876543210',
                'password' => Hash::make('admin123'),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );
    }
}
