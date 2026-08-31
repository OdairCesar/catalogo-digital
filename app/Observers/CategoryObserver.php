<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        Cache::forget(SitemapBuilder::cacheKey());
    }
}
