<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => SectionType::Testimonial,
            'title' => null,
            'content' => fake()->sentence(),
            'status' => PageStatus::Published,
            'sort_order' => 0,
            'data' => [],
            'extra_fields' => null,
        ];
    }

    public function type(SectionType $type): self
    {
        return $this->state(['type' => $type]);
    }

    public function draft(): self
    {
        return $this->state(['status' => PageStatus::Draft]);
    }

    public function forProduct(Product $product): self
    {
        return $this->state(['product_id' => $product->id]);
    }

    public function published(): self
    {
        return $this->state(['status' => PageStatus::Published]);
    }

    public function portfolio(): self
    {
        return $this->type(SectionType::Portfolio)->state(function (): array {
            $title = ucfirst(fake()->unique()->words(3, true));

            return [
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => fake()->paragraph(),
                'content' => fake()->paragraphs(3, true),
                'status' => PageStatus::Draft,
                'data' => ['external_url' => fake()->url()],
            ];
        });
    }
}
