<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 500);
        $hasSale = fake()->boolean(30);

        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->bothify('VAR-####??##')),
            'price' => $price,
            'sale_price' => $hasSale ? $price * 0.85 : null,
            'stock_quantity' => fake()->numberBetween(0, 100),
            'image' => null,
            'attribute_values' => [
                'color' => fake()->numberBetween(1, 10),
                'size' => fake()->numberBetween(1, 5),
            ],
            'is_active' => fake()->boolean(95),
        ];
    }
}
