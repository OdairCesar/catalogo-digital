<?php

namespace App\Observers;

use App\Models\Section;
use Illuminate\Support\Facades\Cache;

class SectionObserver
{
    /**
     * New records default `sort_order` to 0, which would tie with (and sort
     * before, on lower id) the first existing record of the same type.
     * Appending to the end here keeps admin-created rows out of the way of
     * the explicit ordering `SectionSeeder` assigns.
     */
    public function creating(Section $section): void
    {
        if ($section->sort_order === null) {
            $section->sort_order = (Section::query()->ofType($section->type)->max('sort_order') ?? -1) + 1;
        }
    }

    public function saved(Section $section): void
    {
        Cache::forget(Section::cacheKey($section->type));
    }

    public function deleted(Section $section): void
    {
        Cache::forget(Section::cacheKey($section->type));
    }
}
