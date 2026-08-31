<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductImportStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ImportProductsForm;
use App\Jobs\MapProductImportColumns;
use App\Models\ProductImport;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * @property-read Schema $form
 */
class ImportProducts extends Page
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.resources.products.pages.import-products';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Importar planilha de produtos';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return ImportProductsForm::configure($schema)->statePath('data');
    }

    public function analyze(): void
    {
        $data = $this->form->getState();

        $spreadsheetPath = $data['spreadsheet'] ?? null;

        if (! is_string($spreadsheetPath)) {
            return;
        }

        $import = ProductImport::query()->create([
            'company_id' => $data['company_id'],
            'uploaded_by' => auth()->id(),
            'original_filename' => Str::after(basename($spreadsheetPath), '-'),
            'spreadsheet_path' => $spreadsheetPath,
            'status' => ProductImportStatus::Pending,
        ]);

        MapProductImportColumns::dispatch($import);

        Notification::make()
            ->title('Importação iniciada')
            ->body('A IA está analisando as colunas da planilha. Isso pode levar alguns instantes.')
            ->success()
            ->send();

        $this->redirect(ProductResource::getUrl('review-import', ['record' => $import]));
    }
}
