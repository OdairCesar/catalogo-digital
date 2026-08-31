<?php

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Services\Seo\InternalLinkService;

test('otherCitiesForService lists other published cities for the same service, prioritizing the same region', function () {
    $service = Service::factory()->create();
    $bauru = City::factory()->create(['name' => 'Bauru', 'region' => 'Centro-Oeste Paulista']);
    City::factory()->create(['name' => 'Marília', 'region' => 'Centro-Oeste Paulista']);
    City::factory()->create(['name' => 'Campinas', 'region' => 'Grande São Paulo']);

    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $bauru->id)->sole();

    $links = app(InternalLinkService::class)->otherCitiesForService($landingPage);

    expect($links)->toHaveCount(2)
        ->and(collect($links)->pluck('label')->all())->toContain('Marília')
        ->and(collect($links)->pluck('label')->all())->toContain('Campinas')
        ->and($links[0]['label'])->toBe('Marília');
});

test('otherCitiesForService excludes draft landing pages', function () {
    $service = Service::factory()->create();
    $bauru = City::factory()->create();
    City::factory()->create(['status' => PageStatus::Draft]);

    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $bauru->id)->sole();

    expect(app(InternalLinkService::class)->otherCitiesForService($landingPage))->toBeEmpty();
});

test('otherServicesForCity lists other published services available in the same city', function () {
    $sites = Service::factory()->create(['title' => 'Criação de Sites']);
    Service::factory()->create(['title' => 'Desenvolvimento de App']);
    $city = City::factory()->create();

    $landingPage = LandingPage::where('service_id', $sites->id)->where('city_id', $city->id)->sole();

    $links = app(InternalLinkService::class)->otherServicesForCity($landingPage);

    expect($links)->toHaveCount(1)
        ->and($links[0]['label'])->toBe('Desenvolvimento de App');
});

test('clustersForService composes {cidade} tokens in the cluster title instead of leaking them into the link label', function () {
    $service = Service::factory()->create();
    ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'title' => 'Loja Virtual para Empresas de {cidade}',
    ]);

    $links = app(InternalLinkService::class)->clustersForService($service);

    expect($links[0]['label'])->toBe('Loja Virtual para Empresas de sua cidade');
});

test('relatedLinks combines other cities and other services', function () {
    $sites = Service::factory()->create();
    Service::factory()->create();
    $bauru = City::factory()->create();
    City::factory()->create();

    $landingPage = LandingPage::where('service_id', $sites->id)->where('city_id', $bauru->id)->sole();

    expect(app(InternalLinkService::class)->relatedLinks($landingPage))->toHaveCount(2);
});
