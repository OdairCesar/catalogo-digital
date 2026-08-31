<?php

namespace App\Actions\ServiceCluster;

use App\Models\City;
use App\Models\ServiceCluster;

final class SyncClusterLandingPagesForCity
{
    public function __construct(
        private readonly SyncClusterLandingPage $syncClusterLandingPage,
    ) {}

    public function __invoke(City $city): void
    {
        ServiceCluster::query()->each(function (ServiceCluster $cluster) use ($city): void {
            ($this->syncClusterLandingPage)($cluster, $city);
        });
    }
}
