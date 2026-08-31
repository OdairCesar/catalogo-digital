<?php

namespace Database\Factories;

use App\Actions\Support\GenerateLocationSlug;
use App\Enums\PageStatus;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<LandingPage>
 */
class LandingPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'city_id' => City::factory(),
            'meta_title' => null,
            'meta_description' => null,
            'canonical' => null,
            'robots' => 'index,follow',
            'custom_h1' => null,
            'custom_subtitle' => null,
            'custom_intro' => null,
            'custom_cta' => null,
            'status' => PageStatus::Published,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (LandingPage $landingPage): void {
            $service = $landingPage->service ?? Service::find($landingPage->service_id);

            $landingPage->slug = app(GenerateLocationSlug::class)(
                $service->slug,
                $landingPage->city ?? City::find($landingPage->city_id),
            );
        });
    }

    /**
     * Creating the Service/City pair may already have triggered ServiceObserver/CityObserver
     * to auto-generate this exact LandingPage row, so this reconciles instead of inserting blindly.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes = [], ?Model $parent = null): LandingPage
    {
        $planned = $this->make($attributes, $parent);

        return LandingPage::query()->updateOrCreate(
            ['service_id' => $planned->service_id, 'city_id' => $planned->city_id],
            collect($planned->getAttributes())->except(['id', 'service_id', 'city_id'])->all(),
        );
    }
}
