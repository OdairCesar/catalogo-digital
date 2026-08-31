<?php

use App\Actions\ServiceCluster\SyncClusterLandingPagesForCity;
use App\Actions\ServiceCluster\SyncClusterLandingPagesForCluster;
use App\Enums\PageStatus;
use App\Enums\ServiceClusterStatus;
use App\Models\City;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;

test('creating a service cluster generates a cluster landing page for every existing city', function () {
    $bauru = City::factory()->create(['slug' => 'bauru']);
    $marilia = City::factory()->create(['slug' => 'marilia']);

    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id]);

    expect(ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->count())->toBe(2)
        ->and(ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $bauru->id)->exists())->toBeTrue()
        ->and(ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $marilia->id)->exists())->toBeTrue();
});

test('creating a city generates a cluster landing page for every existing service cluster', function () {
    $service = Service::factory()->create();
    $clusterA = ServiceCluster::factory()->create(['service_id' => $service->id]);
    $clusterB = ServiceCluster::factory()->create(['service_id' => $service->id]);

    $city = City::factory()->create(['slug' => 'campinas']);

    expect(ServiceClusterLandingPage::where('city_id', $city->id)->count())->toBe(2)
        ->and(ServiceClusterLandingPage::where('service_cluster_id', $clusterA->id)->where('city_id', $city->id)->exists())->toBeTrue()
        ->and(ServiceClusterLandingPage::where('service_cluster_id', $clusterB->id)->where('city_id', $city->id)->exists())->toBeTrue();
});

test('cluster landing page is only published when the cluster, its service and the city are all published', function () {
    $service = Service::factory()->create(['status' => PageStatus::Published]);
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id, 'status' => ServiceClusterStatus::Draft]);
    $city = City::factory()->create(['status' => PageStatus::Published]);

    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();

    expect(ServiceClusterLandingPage::published()->whereKey($pivot->id)->exists())->toBeFalse();

    $cluster->update(['status' => ServiceClusterStatus::Published]);

    expect(ServiceClusterLandingPage::published()->whereKey($pivot->id)->exists())->toBeTrue();
});

test('re-running the cluster sync actions is idempotent and never duplicates a cluster landing page', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id]);
    $city = City::factory()->create();

    app(SyncClusterLandingPagesForCluster::class)($cluster);
    app(SyncClusterLandingPagesForCity::class)($city);

    expect(ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->count())->toBe(1);
});

test('renaming a cluster slug cascades to all of its cluster landing pages', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id, 'slug' => 'consultoria-antiga']);
    $city = City::factory()->create(['slug' => 'bauru']);

    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();
    expect($pivot->slug)->toBe('consultoria-antiga-em-bauru');

    $cluster->update(['slug' => 'consultoria-nova']);

    expect($pivot->fresh()->slug)->toBe('consultoria-nova-em-bauru');
});
