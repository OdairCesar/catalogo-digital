<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\PageStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Service;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * @extends EditRecord<Service>
 */
class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewOnLandingAction::make(
                url: fn (): string => route('services.show', $this->record),
                visible: fn (): bool => $this->getRecord()->status === PageStatus::Published,
            ),
            DeleteAction::make(),
        ];
    }
}
