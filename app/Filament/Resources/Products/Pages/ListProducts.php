<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar planilha')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->url(ProductResource::getUrl('import')),
            CreateAction::make(),
        ];
    }
}
