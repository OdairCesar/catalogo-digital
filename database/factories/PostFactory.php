<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence();

        return [
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->paragraph(),
            'content' => collect($this->faker->paragraphs(5))
                ->map(fn (string $paragraph): string => "<p>{$paragraph}</p>")
                ->implode(''),
            'cover_image' => null,
            'tags' => $this->faker->words(3),
            'status' => PostStatus::Draft,
            'ai_brief' => null,
            'ai_model' => null,
            'ai_error' => null,
            'meta_title' => null,
            'meta_description' => null,
            'canonical' => null,
            'robots' => 'index,follow',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function generating(): static
    {
        return $this->state(fn (): array => [
            'title' => null,
            'slug' => null,
            'excerpt' => null,
            'content' => null,
            'status' => PostStatus::Generating,
        ]);
    }
}
