<?php

namespace Database\Factories;

use App\Enums\ServiceClusterStatus;
use App\Models\Service;
use App\Models\ServiceCluster;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCluster>
 */
class ServiceClusterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'service_id' => Service::factory(),
            'slug' => Str::slug($title),
            'name' => ucfirst($title),
            'title' => ucfirst($title),
            'subtitle' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(2, true),
            'benefits' => $this->faker->sentences(4),
            'faq' => collect(range(1, 2))->map(fn () => [
                'question' => $this->faker->sentence().'?',
                'answer' => $this->faker->paragraph(),
            ])->all(),
            'keywords' => $this->faker->words(5),
            'hero_image' => null,
            'status' => ServiceClusterStatus::Published,
        ];
    }
}
