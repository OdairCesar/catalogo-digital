<?php

namespace App\Observers;

use App\Models\ServiceClusterLandingPage;
use App\Services\Landing\ServiceClusterViewModelFactory;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class ServiceClusterLandingPageObserver
{
    public function saved(ServiceClusterLandingPage $serviceClusterLandingPage): void
    {
        Cache::forget(ServiceClusterViewModelFactory::cacheKey((string) $serviceClusterLandingPage->slug));
        Cache::forget(SitemapBuilder::cacheKey());
    }
}
