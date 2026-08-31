<?php

namespace App\Filament\Resources\ServiceClusterLandingPages\Pages;

use App\Filament\Resources\ServiceClusterLandingPages\ServiceClusterLandingPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceClusterLandingPages extends ListRecords
{
    protected static string $resource = ServiceClusterLandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
