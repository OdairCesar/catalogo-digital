<?php

namespace App\Filament\Resources\ServiceClusters\Pages;

use App\Enums\ServiceClusterStatus;
use App\Filament\Resources\ServiceClusters\ServiceClusterResource;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\ServiceCluster;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * @extends EditRecord<ServiceCluster>
 */
class EditServiceCluster extends EditRecord
{
    protected static string $resource = ServiceClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewOnLandingAction::make(
                url: fn (): string => route('services.clusters.show', [$this->getRecord()->service, $this->getRecord()]),
                visible: fn (): bool => $this->getRecord()->status === ServiceClusterStatus::Published,
                label: 'Ver cluster',
            ),
            DeleteAction::make(),
        ];
    }
}
