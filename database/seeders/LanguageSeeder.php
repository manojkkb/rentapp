<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'code' => 'en', 'native_name' => 'English', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Hindi', 'code' => 'hi', 'native_name' => 'हिन्दी', 'sort_order' => 2],
            ['name' => 'Bengali', 'code' => 'bn', 'native_name' => 'বাংলা', 'sort_order' => 3],
            ['name' => 'Marathi', 'code' => 'mr', 'native_name' => 'मराठी', 'sort_order' => 4],
            ['name' => 'Telugu', 'code' => 'te', 'native_name' => 'తెలుగు', 'sort_order' => 5],
            ['name' => 'Tamil', 'code' => 'ta', 'native_name' => 'தமிழ்', 'sort_order' => 6],
            ['name' => 'Gujarati', 'code' => 'gu', 'native_name' => 'ગુજરાતી', 'sort_order' => 7],
            ['name' => 'Urdu', 'code' => 'ur', 'native_name' => 'اردو', 'sort_order' => 8],
            ['name' => 'Kannada', 'code' => 'kn', 'native_name' => 'ಕನ್ನಡ', 'sort_order' => 9],
            ['name' => 'Odia', 'code' => 'or', 'native_name' => 'ଓଡ଼ିଆ', 'sort_order' => 10],
            ['name' => 'Malayalam', 'code' => 'ml', 'native_name' => 'മലയാളം', 'sort_order' => 11],
            ['name' => 'Punjabi', 'code' => 'pa', 'native_name' => 'ਪੰਜਾਬੀ', 'sort_order' => 12],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->updateOrInsert(
                ['code' => $language['code']],
                [
                    'name' => $language['name'],
                    'native_name' => $language['native_name'],
                    'sort_order' => $language['sort_order'],
                    'is_active' => true,
                    'is_default' => $language['is_default'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
