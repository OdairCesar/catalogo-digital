<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAvailability>
 */
class ProductAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'weekday' => $this->faker->numberBetween(0, 6),
            'starts_at' => '09:00:00',
            'ends_at' => '18:00:00',
        ];
    }
}
