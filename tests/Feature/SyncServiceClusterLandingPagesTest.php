<?php

use App\Models\City;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;

test('it backfills cluster landing pages missing for a city without duplicating the ones that already exist', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id]);
    $cityA = City::factory()->create();
    $cityB = City::factory()->create();

    expect(ServiceClusterLandingPage::count())->toBe(2);

    ServiceClusterLandingPage::where('city_id', $cityB->id)->delete();

    $this->artisan('app:sync-service-cluster-landing-pages')->assertExitCode(0);

    expect(ServiceClusterLandingPage::count())->toBe(2)
        ->and(ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $cityA->id)->count())->toBe(1)
        ->and(ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $cityB->id)->count())->toBe(1);
});

test('running it twice does not create duplicate cluster landing pages', function () {
    $service = Service::factory()->create();
    ServiceCluster::factory()->create(['service_id' => $service->id]);
    City::factory()->create();

    $before = ServiceClusterLandingPage::count();

    $this->artisan('app:sync-service-cluster-landing-pages')->assertExitCode(0);
    $this->artisan('app:sync-service-cluster-landing-pages')->assertExitCode(0);

    expect(ServiceClusterLandingPage::count())->toBe($before);
});
