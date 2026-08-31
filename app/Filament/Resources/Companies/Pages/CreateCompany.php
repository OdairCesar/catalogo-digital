<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * There is only ever one company in practice — block direct access to
     * the create page once a record already exists. Authorization still
     * runs first, so a user without create access gets a 403 rather than
     * being silently redirected to the edit page.
     */
    public function mount(): void
    {
        $this->authorizeAccess();

        $company = Company::query()->first();

        if ($company !== null) {
            $this->redirect(CompanyResource::getUrl('edit', ['record' => $company]));

            return;
        }

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }
}
