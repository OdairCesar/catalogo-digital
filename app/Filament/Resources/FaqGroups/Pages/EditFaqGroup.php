<?php

namespace App\Filament\Resources\FaqGroups\Pages;

use App\Filament\Resources\FaqGroups\FaqGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFaqGroup extends EditRecord
{
    protected static string $resource = FaqGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
