<?php

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;

test('service can be created with json fields cast to arrays', function () {
    $service = Service::factory()->create([
        'benefits' => ['Benefício A', 'Benefício B'],
    ]);

    expect($service->benefits)->toBeArray()
        ->and($service->benefits)->toContain('Benefício A')
        ->and($service->status)->toBe(PageStatus::Published);
});

test('city can be created with decimal and integer casts', function () {
    $city = City::factory()->create(['population' => 123456]);

    expect($city->population)->toBe(123456)
        ->and($city->status)->toBe(PageStatus::Published);
});

test('landing page belongs to a service and a city and resolves route binding by slug', function () {
    // Creating the City auto-generates the LandingPage via CityObserver (see SyncTest for that behavior).
    $service = Service::factory()->create(['slug' => 'desenvolvimento-de-sistemas-web']);
    $city = City::factory()->create(['slug' => 'bauru']);

    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    expect($landingPage->slug)->toBe('desenvolvimento-de-sistemas-web-em-bauru')
        ->and($landingPage->service->is($service))->toBeTrue()
        ->and($landingPage->city->is($city))->toBeTrue()
        ->and($landingPage->getRouteKeyName())->toBe('slug');
});
