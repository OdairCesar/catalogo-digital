<?php

namespace App\Filament\Resources\ToolSubmissions\Pages;

use App\Filament\Resources\ToolSubmissions\ToolSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditToolSubmission extends EditRecord
{
    protected static string $resource = ToolSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
