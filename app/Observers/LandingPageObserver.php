<?php

namespace App\Observers;

use App\Models\LandingPage;
use App\Services\Landing\LandingPageViewModelFactory;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class LandingPageObserver
{
    public function saved(LandingPage $landingPage): void
    {
        Cache::forget(LandingPageViewModelFactory::cacheKey($landingPage->slug));
        Cache::forget(SitemapBuilder::cacheKey());
    }
}
