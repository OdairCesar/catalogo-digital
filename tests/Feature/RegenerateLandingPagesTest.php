<?php

use App\Enums\PageStatus;
use App\Jobs\RegenerateLandingPages;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

test('handling the job deletes all landing pages and recreates one per service/city pair, resetting customizations', function () {
    $services = Service::factory()->count(2)->create();
    $cities = City::factory()->count(2)->create();

    $customized = LandingPage::query()->first();
    $customized->update([
        'meta_title' => 'Título customizado',
        'custom_h1' => 'H1 customizado',
        'status' => PageStatus::Draft,
    ]);

    Cache::put(RegenerateLandingPages::LOCK_KEY, true, now()->addMinutes(30));

    Bus::dispatchSync(new RegenerateLandingPages);

    expect(LandingPage::count())->toBe($services->count() * $cities->count());

    LandingPage::all()->each(function (LandingPage $landingPage): void {
        expect($landingPage->meta_title)->toBeNull()
            ->and($landingPage->custom_h1)->toBeNull()
            ->and($landingPage->status)->toBe(PageStatus::Published);
    });

    expect(Cache::has(RegenerateLandingPages::LOCK_KEY))->toBeFalse();
});

test('the failed hook releases the regenerating lock', function () {
    Cache::put(RegenerateLandingPages::LOCK_KEY, true, now()->addMinutes(30));

    (new RegenerateLandingPages)->failed(new Exception('Falha ao regenerar'));

    expect(Cache::has(RegenerateLandingPages::LOCK_KEY))->toBeFalse();
});
