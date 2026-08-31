<?php

namespace App\Observers;

use App\Models\Company;
use Illuminate\Support\Facades\Cache;

class CompanyObserver
{
    public function saved(Company $company): void
    {
        Cache::forget(Company::cacheKey());
    }

    public function deleted(Company $company): void
    {
        Cache::forget(Company::cacheKey());
    }
}
