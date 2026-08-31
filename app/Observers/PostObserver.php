<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function saved(Post $post): void
    {
        Cache::forget(SitemapBuilder::cacheKey());
    }
}
