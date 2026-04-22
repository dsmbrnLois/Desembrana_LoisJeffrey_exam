<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed sample products for demo/testing.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'Premium noise-cancelling wireless headphones with 30-hour battery life and crystal-clear audio quality.',
                'price' => 2499.00,
                'stocks' => 50,
                'image' => 'products/product1.png', 
            ],
            [
                'name' => 'USB-C Charging Cable',
                'description' => 'Fast-charging USB-C cable with braided nylon construction for durability. Compatible with all USB-C devices.',
                'price' => 349.00,
                'stocks' => 200,
                'image' => 'products/product2.png',
            ],
            [
                'name' => 'Laptop Stand Adjustable',
                'description' => 'Ergonomic aluminum laptop stand with adjustable height and angle. Keeps your laptop cool while improving posture.',
                'price' => 1299.00,
                'stocks' => 30,
                'image' => 'products/product3.png', 
            ],
            [
                'name' => 'Mechanical Keyboard RGB',
                'description' => 'Full-size mechanical keyboard with RGB backlighting, Cherry MX switches, and programmable macros.',
                'price' => 3499.00,
                'stocks' => 25,
                'image' => 'products/product4.png',
            ],
            [
                'name' => 'Wireless Mouse Ergonomic',
                'description' => 'Ergonomic wireless mouse with silent clicks, adjustable DPI, and comfortable grip design.',
                'price' => 899.00,
                'stocks' => 75,
                'image' => 'products/product5.png',
            ],
            [
                'name' => 'Portable Power Bank 20000mAh',
                'description' => 'High-capacity power bank with dual USB ports and fast charging support. Charges your phone up to 5 times.',
                'price' => 1599.00,
                'stocks' => 100,
                'image' => 'products/product6.png',
            ],
            [
                'name' => 'Smart Watch Fitness Tracker',
                'description' => 'Feature-rich smartwatch with heart rate monitoring, GPS tracking, and 7-day battery life.',
                'price' => 4999.00,
                'stocks' => 15,
                'image' => 'products/product7.png',
            ],
            [
                'name' => 'Webcam 1080p HD',
                'description' => 'Full HD webcam with built-in microphone, auto-focus, and wide-angle lens for clear video calls.',
                'price' => 1899.00,
                'stocks' => 40,
                'image' => 'products/product8.png',
            ],
            [
                'name' => 'Phone Case Protective',
                'description' => 'Military-grade protective phone case with shock-absorbent corners and slim profile.',
                'price' => 599.00,
                'stocks' => 150,
            ],
            [
                'name' => 'LED Desk Lamp Touch',
                'description' => 'Modern LED desk lamp with touch controls, adjustable brightness, and USB charging port.',
                'price' => 1199.00,
                'stocks' => 60,
            ],
            [
                'name' => 'Desk Organizer Bamboo',
                'description' => 'Premium bamboo desk organizer with multiple compartments for pens, phones, and office supplies.',
                'price' => 799.00,
                'stocks' => 45,
            ],
            [
                'name' => 'Screen Protector Tempered Glass',
                'description' => 'Ultra-thin tempered glass screen protector with 9H hardness and anti-fingerprint coating.',
                'price' => 249.00,
                'stocks' => 0,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}