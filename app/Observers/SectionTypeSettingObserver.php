<?php

namespace App\Observers;

use App\Models\SectionTypeSetting;
use Illuminate\Support\Facades\Cache;

class SectionTypeSettingObserver
{
    public function saved(SectionTypeSetting $setting): void
    {
        Cache::forget(SectionTypeSetting::CACHE_KEY);
    }

    public function deleted(SectionTypeSetting $setting): void
    {
        Cache::forget(SectionTypeSetting::CACHE_KEY);
    }
}
