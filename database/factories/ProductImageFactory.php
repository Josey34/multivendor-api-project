<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'image_path' => 'products/' . fake()->uuid() . '.jpg',
            'thumbnail_path' => 'products/thumbnails/' . fake()->uuid() . '.jpg',
            'is_primary' => false,
            'order' => fake()->numberBetween(1, 10),
        ];
    }

    /**
     * State for primary image
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'order' => 0,
        ]);
    }
}
