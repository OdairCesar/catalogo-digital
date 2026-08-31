<?php

use App\Enums\ServiceClusterStatus;
use App\Models\City;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Services\Landing\ServiceClusterViewModelFactory;

test('it builds a fully composed view model for a cluster landing page', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'status' => ServiceClusterStatus::Published,
        'title' => 'Consultoria Especializada',
        'subtitle' => 'Suporte dedicado para {cidade}',
    ]);
    $city = City::factory()->create(['name' => 'Bauru']);
    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();

    $vm = app(ServiceClusterViewModelFactory::class)->makeForClusterCity($pivot);

    expect($vm->h1)->toBe('Consultoria Especializada em Bauru')
        ->and($vm->subtitle)->toBe('Suporte dedicado para Bauru')
        ->and($vm->breadcrumbs)->toHaveCount(4)
        ->and($vm->jsonLd)->not->toBeEmpty();
});

test('it caches the cluster landing page view model and reflects changes after an update invalidates the cache key', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id]);
    $city = City::factory()->create();
    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();

    $factory = app(ServiceClusterViewModelFactory::class);

    $first = $factory->makeForClusterCity($pivot);
    expect($first->h1)->not->toBe('H1 customizado');

    $pivot->update(['custom_h1' => 'H1 customizado']);

    $second = $factory->makeForClusterCity($pivot->fresh());
    expect($second->h1)->toBe('H1 customizado');
});
