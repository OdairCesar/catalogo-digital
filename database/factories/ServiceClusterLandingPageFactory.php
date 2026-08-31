<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<ServiceClusterLandingPage>
 */
class ServiceClusterLandingPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_cluster_id' => ServiceCluster::factory(),
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

    /**
     * Creating the ServiceCluster/City pair may already have triggered ServiceClusterObserver/CityObserver
     * to auto-generate this exact row, so this reconciles instead of inserting blindly.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes = [], ?Model $parent = null): ServiceClusterLandingPage
    {
        $planned = $this->make($attributes, $parent);

        return ServiceClusterLandingPage::query()->updateOrCreate(
            ['service_cluster_id' => $planned->service_cluster_id, 'city_id' => $planned->city_id],
            collect($planned->getAttributes())->except(['id', 'service_cluster_id', 'city_id'])->all(),
        );
    }
}
