<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->city();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'state_id' => State::query()->inRandomOrder()->value('id') ?? State::factory(),
            'region' => $this->faker->randomElement(['Centro-Oeste Paulista', 'Interior de São Paulo', 'Grande São Paulo']),
            'population' => $this->faker->numberBetween(20_000, 1_200_000),
            'gdp' => $this->faker->randomFloat(2, 100_000_000, 9_000_000_000),
            'latitude' => $this->faker->latitude(-23, -21),
            'longitude' => $this->faker->longitude(-50, -47),
            'intro' => $this->faker->paragraph(),
            'business_text' => $this->faker->paragraph(),
            'status' => PageStatus::Published,
        ];
    }
}
