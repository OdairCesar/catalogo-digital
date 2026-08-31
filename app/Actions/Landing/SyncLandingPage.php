<?php

namespace App\Actions\Landing;

use App\Actions\Support\GenerateLocationSlug;
use App\Enums\PageStatus;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;

final class SyncLandingPage
{
    public function __construct(
        private readonly GenerateLocationSlug $generateLocationSlug,
    ) {}

    public function __invoke(Service $service, City $city): void
    {
        LandingPage::query()->firstOrCreate(
            ['service_id' => $service->id, 'city_id' => $city->id],
            [
                'slug' => ($this->generateLocationSlug)($service->slug, $city),
                'status' => PageStatus::Published,
            ],
        );
    }
}
