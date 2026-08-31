<?php

namespace App\Filament\Resources\ProductInventories\Pages;

use App\Filament\Resources\ProductInventories\ProductInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductInventory extends EditRecord
{
    protected static string $resource = ProductInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
