<?php

namespace App\Actions\ServiceCluster;

use App\Actions\Support\GenerateLocationSlug;
use App\Models\ServiceCluster;

final class RenameClusterLandingPages
{
    public function __construct(
        private readonly GenerateLocationSlug $generateLocationSlug,
    ) {}

    public function __invoke(ServiceCluster $cluster): void
    {
        $cluster->loadMissing('landingPages.city');

        foreach ($cluster->landingPages as $landingPage) {
            $landingPage->update(['slug' => ($this->generateLocationSlug)((string) $cluster->slug, $landingPage->city)]);
        }
    }
}
