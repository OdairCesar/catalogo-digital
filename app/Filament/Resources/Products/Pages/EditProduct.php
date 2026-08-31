<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\PageStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewOnLandingAction::make(
                url: fn (): string => route('products.show', $this->record),
                visible: fn (): bool => $this->getRecord()->status === PageStatus::Published
                    && $this->getRecord()->company->status === PageStatus::Published,
                label: 'Ver produto',
            ),
            DeleteAction::make(),
        ];
    }

    public function getRecord(): Product
    {
        abort_unless($this->record instanceof Product, 404);

        return $this->record;
    }
}
