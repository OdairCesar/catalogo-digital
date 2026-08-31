<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Enums\ProductCondition;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst($this->faker->unique()->words(3, true));

        return [
            'company_id' => Company::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'sku' => null,
            'description' => $this->faker->paragraph(),
            'brand' => null,
            'gtin' => null,
            'mpn' => null,
            'condition' => ProductCondition::New,
            'base_price' => $this->faker->randomFloat(2, 10, 500),
            'base_sale_price' => null,
            'base_stock' => $this->faker->numberBetween(0, 100),
            'weight_kg' => null,
            'height_cm' => null,
            'width_cm' => null,
            'length_cm' => null,
            'cover_image' => null,
            'images' => null,
            'status' => PageStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PageStatus::Published,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => PageStatus::Draft,
        ]);
    }
}
