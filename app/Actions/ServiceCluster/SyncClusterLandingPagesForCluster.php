<?php

namespace App\Actions\ServiceCluster;

use App\Models\City;
use App\Models\ServiceCluster;

final class SyncClusterLandingPagesForCluster
{
    public function __construct(
        private readonly SyncClusterLandingPage $syncClusterLandingPage,
    ) {}

    public function __invoke(ServiceCluster $cluster): void
    {
        City::query()->each(function (City $city) use ($cluster): void {
            ($this->syncClusterLandingPage)($cluster, $city);
        });
    }
}
