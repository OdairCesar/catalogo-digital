<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->company());

        return [
            'name' => $name,
            'site_name' => null,
            'slug' => Str::slug($name),
            'cnpj' => null,
            'whatsapp' => null,
            'email' => null,
            'address_zip_code' => null,
            'address_street' => null,
            'address_number' => null,
            'address_complement' => null,
            'address_neighborhood' => null,
            'address_city' => null,
            'address_state' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'opening_hours' => null,
            'short_description' => null,
            'logo' => null,
            'favicon' => null,
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
