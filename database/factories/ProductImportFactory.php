<?php

namespace Database\Factories;

use App\Enums\ProductImportStatus;
use App\Models\Company;
use App\Models\ProductImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImport>
 */
class ProductImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'uploaded_by' => User::factory(),
            'original_filename' => $this->faker->slug().'.xlsx',
            'spreadsheet_path' => 'product-imports/'.$this->faker->uuid().'.xlsx',
            'status' => ProductImportStatus::Pending,
            'mapping' => null,
            'result' => null,
            'ai_error' => null,
        ];
    }

    public function awaitingReview(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductImportStatus::AwaitingReview,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductImportStatus::Completed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductImportStatus::Failed,
            'ai_error' => $this->faker->sentence(),
        ]);
    }
}
