<?php

namespace App\Console\Commands;

use App\Actions\ServiceCluster\SyncClusterLandingPagesForCluster;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-service-cluster-landing-pages')]
#[Description('Backfill missing service_cluster_landing_pages rows for every cluster/city combination (safe to re-run, never duplicates)')]
class SyncServiceClusterLandingPages extends Command
{
    public function handle(SyncClusterLandingPagesForCluster $sync): int
    {
        $clusters = ServiceCluster::query()->get();

        if ($clusters->isEmpty()) {
            $this->info('No service clusters found.');

            return self::SUCCESS;
        }

        $before = ServiceClusterLandingPage::query()->count();

        $this->withProgressBar($clusters, fn (ServiceCluster $cluster) => $sync($cluster));
        $this->newLine();

        $created = ServiceClusterLandingPage::query()->count() - $before;

        $this->info("Created {$created} missing cluster landing page(s).");

        return self::SUCCESS;
    }
}
