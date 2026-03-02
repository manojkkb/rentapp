<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = Vendor::all();

        if ($vendors->isEmpty()) {
            $this->command->warn('⚠️  No vendors found. Please run VendorSeeder first.');
            return;
        }

        // Define main categories and their subcategories
        $categoriesData = [
            'Electronics' => [
                'Cameras & Photography',
                'Laptops & Computers',
                'Audio Equipment',
                'Video Equipment',
                'Gaming Consoles',
                'Projectors & Screens',
                'Drones',
                'Mobile Devices',
            ],
            'Furniture' => [
                'Chairs & Seating',
                'Tables',
                'Sofas & Couches',
                'Storage & Shelving',
                'Outdoor Furniture',
                'Office Furniture',
                'Beds & Mattresses',
            ],
            'Sports Equipment' => [
                'Cycling',
                'Camping & Hiking',
                'Water Sports',
                'Gym Equipment',
                'Team Sports',
                'Winter Sports',
                'Adventure Sports',
            ],
            'Event Equipment' => [
                'Tents & Canopies',
                'Party Supplies',
                'Lighting',
                'Stage Equipment',
                'Catering Equipment',
                'Decoration',
                'Sound Systems',
            ],
            'Tools & Machinery' => [
                'Power Tools',
                'Hand Tools',
                'Construction Equipment',
                'Gardening Tools',
                'Cleaning Equipment',
                'Automotive Tools',
                'Welding Equipment',
            ],
            'Vehicles' => [
                'Cars',
                'Bikes & Scooters',
                'Trucks & Vans',
                'Luxury Vehicles',
                'Electric Vehicles',
                'Commercial Vehicles',
            ],
            'Clothing & Costumes' => [
                'Formal Wear',
                'Traditional Wear',
                'Costumes',
                'Accessories',
                'Wedding Attire',
                'Kids Wear',
            ],
            'Home Appliances' => [
                'Kitchen Appliances',
                'Cleaning Appliances',
                'Air Conditioners',
                'Heaters',
                'Water Purifiers',
                'Washing Machines',
            ],
        ];

        foreach ($vendors as $vendor) {
            $this->command->info("🏪 Creating categories for vendor: {$vendor->name}");
            $categoryCount = 0;
            $subcategoryCount = 0;

            foreach ($categoriesData as $mainCategoryName => $subcategories) {
                // Create main category
                $mainCategory = Category::create([
                    'vendor_id' => $vendor->id,
                    'parent_id' => null,
                    'name' => $mainCategoryName,
                    'slug' => Str::slug($mainCategoryName),
                    'is_active' => true,
                ]);
                $categoryCount++;

                // Create subcategories
                foreach ($subcategories as $subcategoryName) {
                    Category::create([
                        'vendor_id' => $vendor->id,
                        'parent_id' => $mainCategory->id,
                        'name' => $subcategoryName,
                        'slug' => Str::slug($subcategoryName),
                        'is_active' => true,
                    ]);
                    $subcategoryCount++;
                }
            }

            $this->command->info("   ✅ Created {$categoryCount} main categories");
            $this->command->info("   ✅ Created {$subcategoryCount} subcategories");
        }

        $this->command->info('');
        $this->command->info('🎉 Category seeding completed successfully!');
    }
}
