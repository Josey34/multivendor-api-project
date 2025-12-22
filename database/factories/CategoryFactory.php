<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(20),
            'image' => null,
            'parent_id' => null,
            'order' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(90), // 90% chance of being true
        ];
    }

    /**
     * State for child categories
     */
    public function child($parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }
}
