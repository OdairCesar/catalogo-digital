<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->state();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'uf' => $this->faker->unique()->stateAbbr(),
            'intro' => $this->faker->paragraph(),
            'business_text' => $this->faker->paragraph(),
            'status' => PageStatus::Published,
        ];
    }
}
