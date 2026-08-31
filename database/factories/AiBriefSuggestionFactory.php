<?php

namespace Database\Factories;

use App\Models\AiBriefSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiBriefSuggestion>
 */
class AiBriefSuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'field' => $this->faker->randomElement(['target_audience', 'search_intent', 'primary_keyword']),
            'value' => $this->faker->unique()->words(3, true),
        ];
    }
}
