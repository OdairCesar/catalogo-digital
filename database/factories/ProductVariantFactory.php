<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
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
            'sku' => strtoupper($this->faker->unique()->bothify('VAR-####')),
            'price' => null,
            'sale_price' => null,
            'stock' => null,
            'weight_kg' => null,
            'height_cm' => null,
            'width_cm' => null,
            'length_cm' => null,
            'image' => null,
            'is_active' => true,
        ];
    }
}
