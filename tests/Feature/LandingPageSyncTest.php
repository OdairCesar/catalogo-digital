<?php

use App\Actions\Landing\SyncLandingPagesForCity;
use App\Actions\Landing\SyncLandingPagesForService;
use App\Enums\PageStatus;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;

test('creating a service generates a landing page for every existing city', function () {
    $bauru = City::factory()->create(['slug' => 'bauru']);
    $marilia = City::factory()->create(['slug' => 'marilia']);

    $service = Service::factory()->create(['slug' => 'criacao-de-sites']);

    expect(LandingPage::where('service_id', $service->id)->count())->toBe(2)
        ->and(LandingPage::where('service_id', $service->id)->where('city_id', $bauru->id)->sole()->slug)
        ->toBe('criacao-de-sites-em-bauru')
        ->and(LandingPage::where('service_id', $service->id)->where('city_id', $marilia->id)->sole()->slug)
        ->toBe('criacao-de-sites-em-marilia');
});

test('creating a city generates a landing page for every existing service', function () {
    $sites = Service::factory()->create(['slug' => 'criacao-de-sites']);
    $apps = Service::factory()->create(['slug' => 'desenvolvimento-de-app']);

    $city = City::factory()->create(['slug' => 'campinas']);

    expect(LandingPage::where('city_id', $city->id)->count())->toBe(2)
        ->and(LandingPage::where('service_id', $sites->id)->where('city_id', $city->id)->sole()->slug)
        ->toBe('criacao-de-sites-em-campinas')
        ->and(LandingPage::where('service_id', $apps->id)->where('city_id', $city->id)->sole()->slug)
        ->toBe('desenvolvimento-de-app-em-campinas');
});

test('landing page is only published when both the service and the city are published', function () {
    $draftService = Service::factory()->create(['status' => PageStatus::Draft]);
    $publishedCity = City::factory()->create(['status' => PageStatus::Published]);

    $landingPage = LandingPage::where('service_id', $draftService->id)->where('city_id', $publishedCity->id)->sole();

    expect(LandingPage::published()->whereKey($landingPage->id)->exists())->toBeFalse();
});

test('publishing the city later makes the already-created landing page visible without touching the landing page row', function () {
    $service = Service::factory()->create(['status' => PageStatus::Published]);
    $city = City::factory()->create(['status' => PageStatus::Draft]);

    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    expect(LandingPage::published()->whereKey($landingPage->id)->exists())->toBeFalse();

    $city->update(['status' => PageStatus::Published]);

    expect(LandingPage::published()->whereKey($landingPage->id)->exists())->toBeTrue()
        ->and($landingPage->fresh()->status)->toBe(PageStatus::Published);
});

test('re-running the sync actions is idempotent and never duplicates a landing page', function () {
    $service = Service::factory()->create();
    $city = City::factory()->create();

    app(SyncLandingPagesForService::class)($service);
    app(SyncLandingPagesForCity::class)($city);

    expect(LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->count())->toBe(1);
});
