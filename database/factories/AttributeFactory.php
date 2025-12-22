<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttributeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Color', 'Size', 'Material', 'Style']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(['select', 'radio', 'color']),
        ];
    }
}
