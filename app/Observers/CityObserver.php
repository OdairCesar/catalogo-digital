<?php

namespace App\Observers;

use App\Actions\Landing\SyncLandingPagesForCity;
use App\Actions\ServiceCluster\SyncClusterLandingPagesForCity;
use App\Models\City;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class CityObserver
{
    public function __construct(
        private readonly SyncLandingPagesForCity $syncLandingPagesForCity,
        private readonly SyncClusterLandingPagesForCity $syncClusterLandingPagesForCity,
    ) {}

    public function created(City $city): void
    {
        ($this->syncLandingPagesForCity)($city);
        ($this->syncClusterLandingPagesForCity)($city);
    }

    public function saved(City $city): void
    {
        Cache::forget(SitemapBuilder::cacheKey());
    }
}
