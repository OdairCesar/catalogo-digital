<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Company;
use App\Services\Products\GoogleShoppingFeedBuilder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
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
            ViewOnLandingAction::make(
                url: fn (): string => route('products.feed', Company::current()),
                visible: fn (): bool => Company::current()?->products()->active()->exists() ?? false,
                label: 'Ver XML do Google Shopping',
            ),
            Action::make('refreshGoogleShoppingFeed')
                ->label('Atualizar XML do Google Shopping')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (GoogleShoppingFeedBuilder $feedBuilder): void {
                    Company::query()->each($feedBuilder->forgetCache(...));

                    Notification::make()
                        ->title('Feed do Google Shopping atualizado')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
