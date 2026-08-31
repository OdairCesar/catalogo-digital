<?php

namespace App\Filament\Resources\ProductInventories\Pages;

use App\Filament\Resources\ProductInventories\ProductInventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductInventory extends CreateRecord
{
    protected static string $resource = ProductInventoryResource::class;
}
