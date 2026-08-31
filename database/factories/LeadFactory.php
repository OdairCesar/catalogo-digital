<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => Lead::SOURCE_CONTACT,
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'message' => $this->faker->paragraph(),
            'payload' => ['company' => $this->faker->company()],
            'source_url' => $this->faker->url(),
            'read_at' => null,
        ];
    }
}
