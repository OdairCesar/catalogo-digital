<?php

namespace Database\Factories;

use App\Models\ProductInventory;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductInventory>
 */
class ProductInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => null,
            'product_variant_id' => null,
            'price' => null,
            'sale_price' => null,
            'quantity' => null,
        ];
    }
}
