<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'value' => fake()->word(),
            'color_code' => null,
        ];
    }

    /**
     * State for color values
     */
    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'color_code' => fake()->hexColor(),
        ]);
    }
}
