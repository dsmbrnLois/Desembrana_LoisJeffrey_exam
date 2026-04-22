<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'Wireless Bluetooth Headphones',
            'USB-C Charging Cable',
            'Laptop Stand Adjustable',
            'Mechanical Keyboard RGB',
            'Wireless Mouse Ergonomic',
            'Portable Power Bank 20000mAh',
            'Smart Watch Fitness Tracker',
            'Webcam 1080p HD',
            'Phone Case Protective',
            'Screen Protector Tempered Glass',
            'Desk Organizer Bamboo',
            'LED Desk Lamp Touch',
        ];

        return [
            'name' => fake()->unique()->randomElement($products),
            'description' => fake()->paragraph(2),
            'price' => fake()->randomFloat(2, 99, 4999),
            'stocks' => fake()->numberBetween(0, 200),
            'image' => null,
        ];
    }

    /**
     * Create an out-of-stock product.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stocks' => 0,
        ]);
    }
}
