<?php

namespace App\Filament\Resources\ServiceClusterLandingPages\Pages;

use App\Filament\Resources\ServiceClusterLandingPages\ServiceClusterLandingPageResource;
use App\Models\ServiceClusterLandingPage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Route;

/**
 * @extends EditRecord<ServiceClusterLandingPage>
 */
class EditServiceClusterLandingPage extends EditRecord
{
    protected static string $resource = ServiceClusterLandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Ver site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->visible(fn (): bool => Route::has('services.clusters.show'))
                ->url(fn (): string => route('services.clusters.show', [
                    $this->getRecord()->serviceCluster->service,
                    $this->getRecord()->slug,
                ]))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
