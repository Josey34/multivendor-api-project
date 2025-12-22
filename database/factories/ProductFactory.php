<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->randomFloat(2, 10, 1000);
        $hasSale = fake()->boolean(30); // 30% chance of sale

        return [
            'vendor_id' => Vendor::factory()->approved(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'sku' => strtoupper(fake()->bothify('SKU-####??##')),
            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->sentence(15),
            'price' => $price,
            'sale_price' => $hasSale ? $price * 0.8 : null,
            'cost_price' => $price * 0.6,
            'stock_quantity' => fake()->numberBetween(0, 500),
            'low_stock_threshold' => 5,
            'weight' => fake()->randomFloat(2, 0.1, 50),
            'dimensions' => [
                'length' => fake()->numberBetween(10, 100),
                'width' => fake()->numberBetween(10, 100),
                'height' => fake()->numberBetween(5, 50),
            ],
            'is_active' => fake()->boolean(90),
            'is_featured' => fake()->boolean(10),
            'stock_status' => fake()->randomElement(['in_stock', 'out_of_stock', 'on_backorder']),
            'rating' => fake()->randomFloat(2, 3, 5),
            'total_reviews' => fake()->numberBetween(0, 200),
            'total_sales' => fake()->numberBetween(0, 1000),
            'views_count' => fake()->numberBetween(0, 5000),
            'meta_data' => [
                'meta_title' => ucwords($name),
                'meta_description' => fake()->sentence(20),
                'meta_keywords' => implode(', ', fake()->words(5)),
            ],
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * State for featured products
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * State for out of stock products
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'stock_status' => 'out_of_stock',
        ]);
    }
}
