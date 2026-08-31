<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Company;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * No DeleteAction: there is only ever one company, and deleting it
     * would take the site's own institutional data down with it.
     */
    protected function getHeaderActions(): array
    {
        return [
            ViewOnLandingAction::make(
                url: fn (): string => route('products.feed', $this->record),
                visible: fn (): bool => $this->getRecord()->products()->active()->exists(),
                label: 'Ver feed do Google Shopping',
            ),
        ];
    }

    public function getRecord(): Company
    {
        abort_unless($this->record instanceof Company, 404);

        return $this->record;
    }
}
