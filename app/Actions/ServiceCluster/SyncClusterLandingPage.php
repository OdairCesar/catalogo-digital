<?php

namespace App\Actions\ServiceCluster;

use App\Actions\Support\GenerateLocationSlug;
use App\Enums\PageStatus;
use App\Models\City;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;

final class SyncClusterLandingPage
{
    public function __construct(
        private readonly GenerateLocationSlug $generateLocationSlug,
    ) {}

    public function __invoke(ServiceCluster $cluster, City $city): void
    {
        ServiceClusterLandingPage::query()->firstOrCreate(
            ['service_cluster_id' => $cluster->id, 'city_id' => $city->id],
            [
                'slug' => ($this->generateLocationSlug)((string) $cluster->slug, $city),
                'status' => PageStatus::Published,
            ],
        );
    }
}
