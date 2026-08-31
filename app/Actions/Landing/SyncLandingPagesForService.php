<?php

namespace App\Actions\Landing;

use App\Models\City;
use App\Models\Service;

final class SyncLandingPagesForService
{
    public function __construct(
        private readonly SyncLandingPage $syncLandingPage,
    ) {}

    public function __invoke(Service $service): void
    {
        City::query()->each(function (City $city) use ($service): void {
            ($this->syncLandingPage)($service, $city);
        });
    }
}
