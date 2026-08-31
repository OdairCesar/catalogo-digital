<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    /**
     * There is only ever one company in practice, so jump straight to
     * editing it instead of showing a list with a single row.
     */
    public function mount(): void
    {
        parent::mount();

        $companies = Company::query()->limit(2)->get();

        if ($companies->count() === 1) {
            $this->redirect(CompanyResource::getUrl('edit', ['record' => $companies->first()]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => Company::query()->doesntExist()),
        ];
    }
}
