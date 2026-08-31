<?php

namespace App\Filament\Resources\ServiceClusters\Pages;

use App\Filament\Resources\ServiceClusters\ServiceClusterResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListServiceClusters extends ListRecords
{
    protected static string $resource = ServiceClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateWithAi')
                ->label('Gerar cluster')
                ->icon(Heroicon::OutlinedSparkles)
                ->url(fn (): string => ServiceClusterResource::getUrl('generate')),
            CreateAction::make(),
        ];
    }
}
