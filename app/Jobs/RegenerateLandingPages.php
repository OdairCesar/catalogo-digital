<?php

namespace App\Jobs;

use App\Actions\Landing\SyncLandingPagesForService;
use App\Models\LandingPage;
use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RegenerateLandingPages implements ShouldQueue
{
    use Queueable;

    public const string LOCK_KEY = 'landing-pages:regenerating';

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    public function handle(SyncLandingPagesForService $syncLandingPagesForService): void
    {
        LandingPage::query()->delete();

        Service::query()->each(fn (Service $service) => $syncLandingPagesForService($service));

        Cache::forget(self::LOCK_KEY);
    }

    public function failed(?Throwable $exception): void
    {
        Cache::forget(self::LOCK_KEY);
    }
}
