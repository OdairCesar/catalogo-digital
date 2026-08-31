<?php

namespace App\Observers;

use App\Actions\ServiceCluster\RenameClusterLandingPages;
use App\Actions\ServiceCluster\SyncClusterLandingPagesForCluster;
use App\Models\ServiceCluster;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class ServiceClusterObserver
{
    public function __construct(
        private readonly SyncClusterLandingPagesForCluster $syncClusterLandingPagesForCluster,
        private readonly RenameClusterLandingPages $renameClusterLandingPages,
    ) {}

    public function created(ServiceCluster $serviceCluster): void
    {
        ($this->syncClusterLandingPagesForCluster)($serviceCluster);
    }

    public function saved(ServiceCluster $serviceCluster): void
    {
        Cache::forget(SitemapBuilder::cacheKey());

        if ($serviceCluster->wasChanged('slug')) {
            ($this->renameClusterLandingPages)($serviceCluster);
        }
    }
}
