<?php

namespace App\Observers;

use App\Actions\Landing\SyncLandingPagesForService;
use App\Models\Service;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class ServiceObserver
{
    public function __construct(
        private readonly SyncLandingPagesForService $syncLandingPagesForService,
    ) {}

    public function created(Service $service): void
    {
        ($this->syncLandingPagesForService)($service);
    }

    public function saved(Service $service): void
    {
        Cache::forget(SitemapBuilder::cacheKey());
    }
}
