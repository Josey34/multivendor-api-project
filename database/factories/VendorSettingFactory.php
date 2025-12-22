<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'allow_cod' => fake()->boolean(80),
            'allow_returns' => fake()->boolean(70),
            'return_days' => fake()->randomElement([7, 14, 30]),
            'min_order_amount' => fake()->randomFloat(2, 0, 100),
            'business_hours' => [
                'monday' => '9:00 AM - 6:00 PM',
                'tuesday' => '9:00 AM - 6:00 PM',
                'wednesday' => '9:00 AM - 6:00 PM',
                'thursday' => '9:00 AM - 6:00 PM',
                'friday' => '9:00 AM - 6:00 PM',
                'saturday' => '10:00 AM - 4:00 PM',
                'sunday' => 'Closed',
            ],
            'shipping_methods' => [
                'standard' => ['name' => 'Standard Shipping', 'cost' => 5.00],
                'express' => ['name' => 'Express Shipping', 'cost' => 15.00],
            ],
        ];
    }
}
