<?php

namespace App\Actions\Landing;

use App\Models\City;
use App\Models\Service;

final class SyncLandingPagesForCity
{
    public function __construct(
        private readonly SyncLandingPage $syncLandingPage,
    ) {}

    public function __invoke(City $city): void
    {
        Service::query()->each(function (Service $service) use ($city): void {
            ($this->syncLandingPage)($service, $city);
        });
    }
}
