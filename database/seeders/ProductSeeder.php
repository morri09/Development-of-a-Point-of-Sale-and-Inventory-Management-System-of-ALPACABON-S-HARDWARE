<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $categories = [
            ['name' => 'Power Tools', 'description' => 'Electric and battery-powered tools'],
            ['name' => 'Hand Tools', 'description' => 'Manual tools for various tasks'],
            ['name' => 'Plumbing', 'description' => 'Pipes, fittings, and plumbing supplies'],
            ['name' => 'Electrical', 'description' => 'Wires, switches, and electrical components'],
            ['name' => 'Paint & Supplies', 'description' => 'Paints, brushes, and painting accessories'],
            ['name' => 'Fasteners', 'description' => 'Screws, nails, bolts, and anchors'],
            ['name' => 'Safety Equipment', 'description' => 'Protective gear and safety supplies'],
            ['name' => 'Building Materials', 'description' => 'Cement, lumber, and construction materials'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        // Get category IDs
        $powerTools = Category::where('name', 'Power Tools')->first()->id;
        $handTools = Category::where('name', 'Hand Tools')->first()->id;
        $plumbing = Category::where('name', 'Plumbing')->first()->id;
        $electrical = Category::where('name', 'Electrical')->first()->id;
        $paint = Category::where('name', 'Paint & Supplies')->first()->id;
        $fasteners = Category::where('name', 'Fasteners')->first()->id;
        $safety = Category::where('name', 'Safety Equipment')->first()->id;
        $building = Category::where('name', 'Building Materials')->first()->id;

        // Products data - fill in your own details later
        $products = [
            // Power Tools
            [
                'category_id' => $powerTools,
                'name' => 'Cordless Drill 20V',
                'sku' => 'PT-001',
                'description' => 'Powerful cordless drill with lithium battery',
                'price' => 2499.00,
                'stock_quantity' => 15,
                'image' => null, // Add image path later
            ],
            [
                'category_id' => $powerTools,
                'name' => 'Angle Grinder 4"',
                'sku' => 'PT-002',
                'description' => '850W angle grinder for cutting and grinding',
                'price' => 1899.00,
                'stock_quantity' => 12,
                'image' => null,
            ],
            [
                'category_id' => $powerTools,
                'name' => 'Circular Saw 7"',
                'sku' => 'PT-003',
                'description' => 'Heavy duty circular saw for wood cutting',
                'price' => 3299.00,
                'stock_quantity' => 8,
                'image' => null,
            ],
            [
                'category_id' => $powerTools,
                'name' => 'Jigsaw Electric',
                'sku' => 'PT-004',
                'description' => 'Variable speed jigsaw for curved cuts',
                'price' => 1599.00,
                'stock_quantity' => 10,
                'image' => null,
            ],

            // Hand Tools
            [
                'category_id' => $handTools,
                'name' => 'Hammer Claw 16oz',
                'sku' => 'HT-001',
                'description' => 'Steel claw hammer with rubber grip',
                'price' => 299.00,
                'stock_quantity' => 50,
                'image' => null,
            ],
            [
                'category_id' => $handTools,
                'name' => 'Screwdriver Set 8pc',
                'sku' => 'HT-002',
                'description' => 'Phillips and flathead screwdriver set',
                'price' => 449.00,
                'stock_quantity' => 35,
                'image' => null,
            ],
            [
                'category_id' => $handTools,
                'name' => 'Adjustable Wrench 10"',
                'sku' => 'HT-003',
                'description' => 'Chrome vanadium adjustable wrench',
                'price' => 349.00,
                'stock_quantity' => 25,
                'image' => null,
            ],
            [
                'category_id' => $handTools,
                'name' => 'Tape Measure 5m',
                'sku' => 'HT-004',
                'description' => 'Retractable tape measure with lock',
                'price' => 149.00,
                'stock_quantity' => 60,
                'image' => null,
            ],

            // Plumbing
            [
                'category_id' => $plumbing,
                'name' => 'PVC Pipe 1/2" x 10ft',
                'sku' => 'PL-001',
                'description' => 'Schedule 40 PVC pipe',
                'price' => 89.00,
                'stock_quantity' => 100,
                'image' => null,
            ],
            [
                'category_id' => $plumbing,
                'name' => 'Gate Valve 1/2"',
                'sku' => 'PL-002',
                'description' => 'Brass gate valve',
                'price' => 189.00,
                'stock_quantity' => 40,
                'image' => null,
            ],
            [
                'category_id' => $plumbing,
                'name' => 'Teflon Tape',
                'sku' => 'PL-003',
                'description' => 'Thread seal tape for plumbing',
                'price' => 25.00,
                'stock_quantity' => 200,
                'image' => null,
            ],

            // Electrical
            [
                'category_id' => $electrical,
                'name' => 'THHN Wire #12 (per meter)',
                'sku' => 'EL-001',
                'description' => 'Stranded copper wire',
                'price' => 35.00,
                'stock_quantity' => 500,
                'image' => null,
            ],
            [
                'category_id' => $electrical,
                'name' => 'Outlet Duplex',
                'sku' => 'EL-002',
                'description' => 'Standard duplex electrical outlet',
                'price' => 65.00,
                'stock_quantity' => 80,
                'image' => null,
            ],
            [
                'category_id' => $electrical,
                'name' => 'Circuit Breaker 20A',
                'sku' => 'EL-003',
                'description' => 'Single pole circuit breaker',
                'price' => 249.00,
                'stock_quantity' => 30,
                'image' => null,
            ],

            // Paint & Supplies
            [
                'category_id' => $paint,
                'name' => 'Latex Paint White 4L',
                'sku' => 'PA-001',
                'description' => 'Interior latex paint, flat finish',
                'price' => 599.00,
                'stock_quantity' => 25,
                'image' => null,
            ],
            [
                'category_id' => $paint,
                'name' => 'Paint Brush 3"',
                'sku' => 'PA-002',
                'description' => 'Natural bristle paint brush',
                'price' => 89.00,
                'stock_quantity' => 45,
                'image' => null,
            ],
            [
                'category_id' => $paint,
                'name' => 'Paint Roller Set',
                'sku' => 'PA-003',
                'description' => 'Roller with tray and extension pole',
                'price' => 299.00,
                'stock_quantity' => 20,
                'image' => null,
            ],

            // Fasteners
            [
                'category_id' => $fasteners,
                'name' => 'Wood Screws #8 x 1" (100pc)',
                'sku' => 'FA-001',
                'description' => 'Phillips head wood screws',
                'price' => 149.00,
                'stock_quantity' => 75,
                'image' => null,
            ],
            [
                'category_id' => $fasteners,
                'name' => 'Concrete Nails 2" (1kg)',
                'sku' => 'FA-002',
                'description' => 'Hardened steel concrete nails',
                'price' => 129.00,
                'stock_quantity' => 50,
                'image' => null,
            ],
            [
                'category_id' => $fasteners,
                'name' => 'Expansion Bolt 3/8"',
                'sku' => 'FA-003',
                'description' => 'Concrete expansion anchor',
                'price' => 15.00,
                'stock_quantity' => 200,
                'image' => null,
            ],

            // Safety Equipment
            [
                'category_id' => $safety,
                'name' => 'Safety Helmet',
                'sku' => 'SF-001',
                'description' => 'Hard hat with ratchet adjustment',
                'price' => 349.00,
                'stock_quantity' => 30,
                'image' => null,
            ],
            [
                'category_id' => $safety,
                'name' => 'Safety Goggles',
                'sku' => 'SF-002',
                'description' => 'Clear anti-fog safety glasses',
                'price' => 149.00,
                'stock_quantity' => 40,
                'image' => null,
            ],
            [
                'category_id' => $safety,
                'name' => 'Work Gloves Leather',
                'sku' => 'SF-003',
                'description' => 'Heavy duty leather work gloves',
                'price' => 199.00,
                'stock_quantity' => 35,
                'image' => null,
            ],

            // Building Materials
            [
                'category_id' => $building,
                'name' => 'Portland Cement 40kg',
                'sku' => 'BM-001',
                'description' => 'Type I Portland cement',
                'price' => 280.00,
                'stock_quantity' => 100,
                'image' => null,
            ],
            [
                'category_id' => $building,
                'name' => 'Plywood 1/4" x 4x8',
                'sku' => 'BM-002',
                'description' => 'Marine plywood sheet',
                'price' => 650.00,
                'stock_quantity' => 40,
                'image' => null,
            ],
            [
                'category_id' => $building,
                'name' => 'Steel Bar 10mm x 6m',
                'sku' => 'BM-003',
                'description' => 'Deformed steel reinforcing bar',
                'price' => 185.00,
                'stock_quantity' => 150,
                'image' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $product['category_id'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'stock_quantity' => $product['stock_quantity'],
                    'is_active' => true,
                ]
            );
        }
    }
}
