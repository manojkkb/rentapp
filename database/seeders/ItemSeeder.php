<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Items;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
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

        // Define items data organized by main category
        $itemsData = [
            'Electronics' => [
                'Cameras & Photography' => [
                    ['name' => 'Canon EOS R5 Camera', 'price' => 2500, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'Professional mirrorless camera with 45MP sensor'],
                    ['name' => 'Sony A7 III Camera Body', 'price' => 1800, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Full-frame mirrorless camera for photography and video'],
                    ['name' => 'Nikon D850 DSLR', 'price' => 2000, 'price_type' => 'per_day', 'stock' => 2, 'description' => 'High-resolution DSLR camera'],
                    ['name' => 'Manfrotto Carbon Fiber Tripod', 'price' => 300, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Professional tripod for stable shots'],
                    ['name' => 'Ring Light 18 inch', 'price' => 200, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'LED ring light for portrait and product photography'],
                ],
                'Laptops & Computers' => [
                    ['name' => 'MacBook Pro 16" M3 Max', 'price' => 3500, 'price_type' => 'per_day', 'stock' => 4, 'description' => 'High-performance laptop for video editing and development'],
                    ['name' => 'Dell XPS 15 Laptop', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'Premium Windows laptop with 4K display'],
                    ['name' => 'HP Workstation Desktop', 'price' => 2000, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'Powerful desktop for 3D rendering and CAD'],
                    ['name' => 'iPad Pro 12.9" with Pencil', 'price' => 800, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Tablet for digital art and presentations'],
                ],
                'Audio Equipment' => [
                    ['name' => 'Shure SM7B Microphone', 'price' => 500, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Professional broadcast microphone'],
                    ['name' => 'Audio-Technica Wireless Mic System', 'price' => 600, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Wireless lavalier microphone system'],
                    ['name' => 'Zoom H6 Audio Recorder', 'price' => 400, 'price_type' => 'per_day', 'stock' => 4, 'description' => '6-track portable audio recorder'],
                ],
                'Projectors & Screens' => [
                    ['name' => 'Epson 4K Projector', 'price' => 1200, 'price_type' => 'per_day', 'stock' => 3, 'description' => '4K UHD projector for events and presentations'],
                    ['name' => '120" Projection Screen', 'price' => 400, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Portable projection screen with stand'],
                ],
                'Drones' => [
                    ['name' => 'DJI Mavic 3 Pro Drone', 'price' => 3000, 'price_type' => 'per_day', 'stock' => 2, 'description' => 'Professional drone with triple camera system'],
                    ['name' => 'DJI Mini 3 Pro', 'price' => 1200, 'price_type' => 'per_day', 'stock' => 4, 'description' => 'Compact drone with 4K camera'],
                ],
            ],
            'Furniture' => [
                'Chairs & Seating' => [
                    ['name' => 'Herman Miller Aeron Chair', 'price' => 800, 'price_type' => 'per_day', 'stock' => 12, 'description' => 'Ergonomic office chair'],
                    ['name' => 'Banquet Chairs (Set of 10)', 'price' => 500, 'price_type' => 'per_day', 'stock' => 20, 'description' => 'Stackable chairs for events'],
                    ['name' => 'Leather Executive Chair', 'price' => 600, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Premium office chair'],
                ],
                'Tables' => [
                    ['name' => '6ft Banquet Table', 'price' => 300, 'price_type' => 'per_day', 'stock' => 25, 'description' => 'Foldable table for events'],
                    ['name' => 'Round Dining Table (8 seater)', 'price' => 800, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Elegant dining table'],
                    ['name' => 'Standing Desk Electric', 'price' => 1000, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Adjustable height standing desk'],
                ],
                'Sofas & Couches' => [
                    ['name' => '3 Seater Leather Sofa', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'Premium leather sofa'],
                    ['name' => 'Modular Sectional Sofa', 'price' => 2000, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'Configurable sectional sofa'],
                ],
            ],
            'Sports Equipment' => [
                'Cycling' => [
                    ['name' => 'Trek Mountain Bike', 'price' => 800, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Full suspension mountain bike'],
                    ['name' => 'Road Bike Carbon Frame', 'price' => 1200, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Lightweight road bike'],
                    ['name' => 'Electric Bike', 'price' => 1000, 'price_type' => 'per_day', 'stock' => 4, 'description' => 'E-bike with 50km range'],
                ],
                'Camping & Hiking' => [
                    ['name' => '4-Person Family Tent', 'price' => 600, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Waterproof camping tent'],
                    ['name' => 'Sleeping Bag (-10°C)', 'price' => 200, 'price_type' => 'per_day', 'stock' => 20, 'description' => 'Winter sleeping bag'],
                    ['name' => 'Camping Stove Set', 'price' => 150, 'price_type' => 'per_day', 'stock' => 15, 'description' => 'Portable camping stove with gas'],
                    ['name' => 'Hiking Backpack 60L', 'price' => 250, 'price_type' => 'per_day', 'stock' => 12, 'description' => 'Large hiking backpack'],
                ],
                'Gym Equipment' => [
                    ['name' => 'Treadmill Commercial Grade', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'Professional treadmill'],
                    ['name' => 'Adjustable Dumbbell Set', 'price' => 400, 'price_type' => 'per_day', 'stock' => 10, 'description' => '5-50kg adjustable dumbbells'],
                    ['name' => 'Rowing Machine', 'price' => 800, 'price_type' => 'per_day', 'stock' => 4, 'description' => 'Air resistance rowing machine'],
                ],
            ],
            'Event Equipment' => [
                'Tents & Canopies' => [
                    ['name' => '20x30 Party Tent', 'price' => 3500, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Large event tent with walls'],
                    ['name' => '10x10 Pop-up Canopy', 'price' => 500, 'price_type' => 'per_day', 'stock' => 15, 'description' => 'Instant setup canopy'],
                ],
                'Sound Systems' => [
                    ['name' => 'JBL PA System 2000W', 'price' => 2500, 'price_type' => 'per_day', 'stock' => 4, 'description' => 'Professional sound system'],
                    ['name' => 'Portable PA Speaker', 'price' => 800, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Battery powered PA system'],
                ],
                'Lighting' => [
                    ['name' => 'LED Stage Lights (Set of 8)', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'RGB LED wash lights'],
                    ['name' => 'Moving Head Lights (Pair)', 'price' => 2000, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'DMX moving head lights'],
                ],
            ],
            'Tools & Machinery' => [
                'Power Tools' => [
                    ['name' => 'DeWalt Cordless Drill Set', 'price' => 150, 'price_type' => 'per_day', 'stock' => 12, 'description' => '18V drill with accessories'],
                    ['name' => 'Angle Grinder Heavy Duty', 'price' => 120, 'price_type' => 'per_day', 'stock' => 8, 'description' => '230mm angle grinder'],
                    ['name' => 'Impact Driver Kit', 'price' => 140, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'High torque impact driver'],
                ],
                'Construction Equipment' => [
                    ['name' => 'Concrete Mixer 140L', 'price' => 800, 'price_type' => 'per_day', 'stock' => 4, 'description' => 'Electric concrete mixer'],
                    ['name' => 'Scaffolding Tower 6m', 'price' => 1200, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'Mobile scaffolding system'],
                    ['name' => 'Plate Compactor', 'price' => 600, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Vibrating plate compactor'],
                ],
                'Gardening Tools' => [
                    ['name' => 'Lawn Mower Petrol', 'price' => 400, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Self-propelled lawn mower'],
                    ['name' => 'Hedge Trimmer Electric', 'price' => 200, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Electric hedge trimmer'],
                    ['name' => 'Pressure Washer 3000PSI', 'price' => 500, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'High pressure washer'],
                ],
            ],
            'Vehicles' => [
                'Cars' => [
                    ['name' => 'Toyota Fortuner SUV', 'price' => 3500, 'price_type' => 'per_day', 'stock' => 4, 'description' => '7-seater SUV with driver'],
                    ['name' => 'Honda City Sedan', 'price' => 2000, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Comfortable sedan for city travel'],
                    ['name' => 'Maruti Swift Hatchback', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Compact car for local travel'],
                ],
                'Bikes & Scooters' => [
                    ['name' => 'Royal Enfield Classic 350', 'price' => 800, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'Cruiser motorcycle'],
                    ['name' => 'Honda Activa Scooter', 'price' => 400, 'price_type' => 'per_day', 'stock' => 15, 'description' => 'Automatic scooter'],
                ],
                'Trucks & Vans' => [
                    ['name' => 'Tata Ace Mini Truck', 'price' => 2000, 'price_type' => 'per_day', 'stock' => 5, 'description' => 'Small goods carrier'],
                    ['name' => 'Tempo Traveller 12 Seater', 'price' => 3000, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'Tourist vehicle with AC'],
                ],
            ],
            'Clothing & Costumes' => [
                'Formal Wear' => [
                    ['name' => 'Men\'s 3-Piece Suit', 'price' => 800, 'price_type' => 'per_day', 'stock' => 20, 'description' => 'Premium formal suit'],
                    ['name' => 'Women\'s Evening Gown', 'price' => 1200, 'price_type' => 'per_day', 'stock' => 15, 'description' => 'Designer evening gown'],
                    ['name' => 'Tuxedo with Accessories', 'price' => 1000, 'price_type' => 'per_day', 'stock' => 12, 'description' => 'Complete tuxedo set'],
                ],
                'Traditional Wear' => [
                    ['name' => 'Sherwani with Turban', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 25, 'description' => 'Wedding sherwani'],
                    ['name' => 'Designer Lehenga', 'price' => 2500, 'price_type' => 'per_day', 'stock' => 20, 'description' => 'Bridal lehenga choli'],
                    ['name' => 'Silk Saree with Blouse', 'price' => 800, 'price_type' => 'per_day', 'stock' => 30, 'description' => 'Traditional silk saree'],
                ],
            ],
            'Home Appliances' => [
                'Kitchen Appliances' => [
                    ['name' => 'Commercial Oven', 'price' => 1500, 'price_type' => 'per_day', 'stock' => 3, 'description' => 'Large capacity oven'],
                    ['name' => 'Microwave 30L', 'price' => 300, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Convection microwave'],
                    ['name' => 'Food Processor Heavy Duty', 'price' => 400, 'price_type' => 'per_day', 'stock' => 10, 'description' => 'Professional food processor'],
                ],
                'Air Conditioners' => [
                    ['name' => 'Portable AC 1.5 Ton', 'price' => 1000, 'price_type' => 'per_day', 'stock' => 12, 'description' => 'Portable air conditioner'],
                    ['name' => 'Split AC 2 Ton', 'price' => 1200, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Split air conditioner'],
                ],
                'Washing Machines' => [
                    ['name' => 'Front Load Washing Machine 8kg', 'price' => 600, 'price_type' => 'per_day', 'stock' => 6, 'description' => 'Automatic washing machine'],
                    ['name' => 'Top Load Washer 7kg', 'price' => 500, 'price_type' => 'per_day', 'stock' => 8, 'description' => 'Semi-automatic washer'],
                ],
            ],
        ];

        foreach ($vendors as $vendor) {
            $this->command->info("🏪 Creating items for vendor: {$vendor->name}");
            $itemCount = 0;

            foreach ($itemsData as $mainCategoryName => $subcategoriesData) {
                // Find the main category
                $mainCategory = Category::where('vendor_id', $vendor->id)
                    ->where('name', $mainCategoryName)
                    ->whereNull('parent_id')
                    ->first();

                if (!$mainCategory) {
                    continue;
                }

                foreach ($subcategoriesData as $subcategoryName => $items) {
                    // Find the subcategory
                    $subcategory = Category::where('vendor_id', $vendor->id)
                        ->where('name', $subcategoryName)
                        ->where('parent_id', $mainCategory->id)
                        ->first();

                    if (!$subcategory) {
                        continue;
                    }

                    // Create items for this subcategory
                    foreach ($items as $itemData) {
                        Items::create([
                            'vendor_id' => $vendor->id,
                            'category_id' => $subcategory->id,
                            'name' => $itemData['name'],
                            'slug' => Str::slug($itemData['name']),
                            'description' => $itemData['description'],
                            'price' => $itemData['price'],
                            'price_type' => $itemData['price_type'],
                            'stock' => $itemData['stock'],
                            'manage_stock' => true,
                            'is_available' => true,
                            'is_active' => true,
                        ]);
                        $itemCount++;
                    }
                }
            }

            $this->command->info("   ✅ Created {$itemCount} items");
        }

        $this->command->info('');
        $this->command->info('🎉 Item seeding completed successfully!');
    }
}
