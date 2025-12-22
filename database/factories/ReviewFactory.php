<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $hasImages = fake()->boolean(20);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'order_id' => null, // ← Changed this
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(2),
            'images' => $hasImages ? [
                'reviews/' . fake()->uuid() . '.jpg',
                'reviews/' . fake()->uuid() . '.jpg',
            ] : null,
            'is_verified_purchase' => fake()->boolean(80),
            'is_approved' => fake()->boolean(95),
        ];
    }

    /**
     * State for verified purchase reviews
     */
    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_verified_purchase' => true,
        ]);
    }

    /**
     * State for 5-star reviews
     */
    public function fiveStar(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => 5,
        ]);
    }
}
