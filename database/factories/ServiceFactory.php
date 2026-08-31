<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'slug' => Str::slug($name),
            'name' => ucfirst($name),
            'title' => ucfirst($name),
            'subtitle' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(2, true),
            'benefits' => $this->faker->sentences(4),
            'faq' => collect(range(1, 3))->map(fn () => [
                'question' => $this->faker->sentence().'?',
                'answer' => $this->faker->paragraph(),
            ])->all(),
            'keywords' => $this->faker->words(5),
            'hero_image' => null,
            'icon' => 'rocket',
            'status' => PageStatus::Published,
        ];
    }
}
