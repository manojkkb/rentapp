<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessCategorySeeder extends Seeder
{
    


    public function run(): void
    {
        // Only seed if table is empty
        if (DB::table('business_categories')->count() > 0) {
            return;
        }
        
        $categories = [

            'Event & Wedding Services',
            'Vehicle Rental',
            'Property Rental',
            'Electronics & Equipment Rental',
            'Furniture Rental',
            'Utensils & Catering Rental',
            'Decoration Services',
            'Sound & DJ Services',
            'Lighting Services',
            'Photography & Videography',
            'Banquet & Venue Rental',
            'Home Services',
            'Beauty & Personal Care',
            'Construction & Industrial Rental',
            'Medical Equipment Rental',
            'Logistics & Transport Services',
            'Corporate Event Services',
            'Entertainment Services',
            'Fashion & Costume Rental',
            'Agriculture Equipment Rental',
            'Pet Services',
            'Education & Training Services',
            'Retail & Local Businesses',
            'IT & Technical Services',
            'Cleaning & Facility Management',

        ];

        foreach ($categories as $category) {

            DB::table('business_categories')->insert([
                'name' => $category,
                'slug' => Str::slug($category),
                'parent_id' => null,
                'description' => $category . ' related services',
                'icon' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}







