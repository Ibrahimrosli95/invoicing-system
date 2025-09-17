<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\Company;
use App\Models\User;

class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test company (Bina Group)
        $company = Company::where('name', 'Bina Group')->first();
        
        if (!$company) {
            $this->command->error('Bina Group company not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Get a user to assign as creator
        $user = User::where('company_id', $company->id)->first();
        
        if (!$user) {
            $this->command->error('No users found for Bina Group. Please run RolePermissionSeeder first.');
            return;
        }

        $this->command->info('Creating pricing categories and items for Bina Group...');

        // Create main categories
        $categories = [
            'electrical' => [
                'name' => 'Electrical Supplies',
                'description' => 'Electrical components and supplies',
                'code' => 'ELEC',
                'color' => '#3B82F6',
                'icon' => 'fas fa-bolt',
            ],
            'plumbing' => [
                'name' => 'Plumbing Materials',
                'description' => 'Pipes, fittings, and plumbing supplies',
                'code' => 'PLUMB',
                'color' => '#06B6D4',
                'icon' => 'fas fa-wrench',
            ],
            'construction' => [
                'name' => 'Construction Materials',
                'description' => 'Building and construction supplies',
                'code' => 'CONST',
                'color' => '#8B5CF6',
                'icon' => 'fas fa-hard-hat',
            ],
            'tools' => [
                'name' => 'Tools & Equipment',
                'description' => 'Hand tools and equipment',
                'code' => 'TOOLS',
                'color' => '#F59E0B',
                'icon' => 'fas fa-tools',
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $key => $categoryData) {
            $category = PricingCategory::create([
                'company_id' => $company->id,
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'code' => $categoryData['code'],
                'color' => $categoryData['color'],
                'icon' => $categoryData['icon'],
                'sort_order' => array_search($key, array_keys($categories)),
                'created_by' => $user->id,
            ]);
            $createdCategories[$key] = $category;
        }

        // Create sample items for each category
        $items = [
            'electrical' => [
                [
                    'name' => 'LED Light Bulb 9W',
                    'item_code' => 'LED-9W-001',
                    'unit' => 'Pcs',
                    'unit_price' => 12.50,
                    'cost_price' => 8.00,
                    'minimum_price' => 10.00,
                    'specifications' => '9W, 3000K Warm White, E27 Base, 800 Lumens',
                    'tags' => ['led', 'bulb', 'energy-saving'],
                    'is_featured' => true,
                ],
                [
                    'name' => 'Power Cable 2.5mm²',
                    'item_code' => 'CABLE-2.5-001',
                    'unit' => 'Meter',
                    'unit_price' => 3.20,
                    'cost_price' => 2.10,
                    'minimum_price' => 2.80,
                    'specifications' => '2.5mm² Twin & Earth Cable, 600/1000V',
                    'tags' => ['cable', 'wiring', 'electrical'],
                ],
                [
                    'name' => 'Wall Socket 13A',
                    'item_code' => 'SOCKET-13A-001',
                    'unit' => 'Pcs',
                    'unit_price' => 8.90,
                    'cost_price' => 5.50,
                    'minimum_price' => 7.00,
                    'specifications' => '13A Single Gang Socket, White, BS1363',
                    'tags' => ['socket', 'outlet', 'wall'],
                    'is_featured' => true,
                ],
            ],
            'plumbing' => [
                [
                    'name' => 'PVC Pipe 4" x 3m',
                    'item_code' => 'PVC-4-3M-001',
                    'unit' => 'Pcs',
                    'unit_price' => 18.50,
                    'cost_price' => 12.30,
                    'minimum_price' => 15.00,
                    'specifications' => '4 inch diameter, 3 meter length, Schedule 40',
                    'tags' => ['pipe', 'pvc', 'drainage'],
                ],
                [
                    'name' => 'Elbow Joint 90° 2"',
                    'item_code' => 'ELBOW-90-2-001',
                    'unit' => 'Pcs',
                    'unit_price' => 4.20,
                    'cost_price' => 2.80,
                    'minimum_price' => 3.50,
                    'specifications' => '2 inch 90 degree elbow, PVC, socket type',
                    'tags' => ['fitting', 'elbow', 'joint'],
                ],
                [
                    'name' => 'Water Tap Single Handle',
                    'item_code' => 'TAP-SINGLE-001',
                    'unit' => 'Pcs',
                    'unit_price' => 35.00,
                    'cost_price' => 22.00,
                    'minimum_price' => 28.00,
                    'specifications' => 'Chrome finish, ceramic cartridge, 1/2" connection',
                    'tags' => ['tap', 'faucet', 'chrome'],
                    'is_featured' => true,
                ],
            ],
            'construction' => [
                [
                    'name' => 'Cement Portland 50kg',
                    'item_code' => 'CEMENT-50KG-001',
                    'unit' => 'Bag',
                    'unit_price' => 18.80,
                    'cost_price' => 15.20,
                    'minimum_price' => 17.00,
                    'specifications' => 'Ordinary Portland Cement, Grade 42.5N, 50kg bag',
                    'tags' => ['cement', 'building', 'concrete'],
                    'track_stock' => true,
                    'stock_quantity' => 100,
                ],
                [
                    'name' => 'Steel Rebar 12mm x 6m',
                    'item_code' => 'REBAR-12-6M-001',
                    'unit' => 'Pcs',
                    'unit_price' => 28.50,
                    'cost_price' => 22.40,
                    'minimum_price' => 25.00,
                    'specifications' => '12mm diameter, 6 meter length, Grade 500',
                    'tags' => ['rebar', 'steel', 'reinforcement'],
                ],
                [
                    'name' => 'Brick Red Common 230x110x76mm',
                    'item_code' => 'BRICK-RED-001',
                    'unit' => 'Pcs',
                    'unit_price' => 0.85,
                    'cost_price' => 0.60,
                    'minimum_price' => 0.75,
                    'specifications' => 'Common red brick, standard size 230x110x76mm',
                    'tags' => ['brick', 'masonry', 'building'],
                    'track_stock' => true,
                    'stock_quantity' => 5000,
                ],
            ],
            'tools' => [
                [
                    'name' => 'Electric Drill 13mm',
                    'item_code' => 'DRILL-13MM-001',
                    'unit' => 'Pcs',
                    'unit_price' => 89.90,
                    'cost_price' => 65.00,
                    'minimum_price' => 75.00,
                    'specifications' => '13mm chuck, 500W motor, variable speed, with carrying case',
                    'tags' => ['drill', 'power-tool', 'electric'],
                    'is_featured' => true,
                ],
                [
                    'name' => 'Measuring Tape 5m',
                    'item_code' => 'TAPE-5M-001',
                    'unit' => 'Pcs',
                    'unit_price' => 12.50,
                    'cost_price' => 8.30,
                    'minimum_price' => 10.00,
                    'specifications' => '5 meter steel measuring tape, metric scale',
                    'tags' => ['measuring', 'tape', 'hand-tool'],
                ],
                [
                    'name' => 'Screwdriver Set 6pcs',
                    'item_code' => 'SCREWDRIVER-SET-001',
                    'unit' => 'Set',
                    'unit_price' => 24.90,
                    'cost_price' => 16.50,
                    'minimum_price' => 20.00,
                    'specifications' => '6 piece set, Phillips and flathead, magnetic tips',
                    'tags' => ['screwdriver', 'hand-tool', 'set'],
                ],
            ],
        ];

        foreach ($items as $categoryKey => $categoryItems) {
            $category = $createdCategories[$categoryKey];
            
            foreach ($categoryItems as $index => $itemData) {
                PricingItem::create([
                    'company_id' => $company->id,
                    'pricing_category_id' => $category->id,
                    'name' => $itemData['name'],
                    'item_code' => $itemData['item_code'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'cost_price' => $itemData['cost_price'],
                    'minimum_price' => $itemData['minimum_price'],
                    'specifications' => $itemData['specifications'],
                    'tags' => $itemData['tags'],
                    'is_featured' => $itemData['is_featured'] ?? false,
                    'track_stock' => $itemData['track_stock'] ?? false,
                    'stock_quantity' => $itemData['stock_quantity'] ?? null,
                    'sort_order' => $index,
                    'created_by' => $user->id,
                ]);
            }
        }

        $this->command->info('Created ' . count($createdCategories) . ' pricing categories');
        $this->command->info('Created ' . array_sum(array_map('count', $items)) . ' pricing items');
        $this->command->info('Pricing book seeded successfully for Bina Group!');
    }
}
