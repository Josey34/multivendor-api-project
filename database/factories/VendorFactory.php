<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    public function definition(): array
    {
        $shopName = fake()->company() . ' Shop';

        return [
            'user_id' => User::factory()->vendor(),
            'shop_name' => $shopName,
            'slug' => Str::slug($shopName) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(3),
            'logo' => null,
            'banner' => null,
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'commission_rate' => fake()->randomFloat(2, 5, 20),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'suspended']),
            'rating' => fake()->randomFloat(2, 3, 5),
            'total_reviews' => fake()->numberBetween(0, 500),
        ];
    }

    /**
     * State for approved vendors
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }
}
