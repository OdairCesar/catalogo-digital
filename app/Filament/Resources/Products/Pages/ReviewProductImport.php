<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductImportStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ReviewProductImportForm;
use App\Jobs\ExecuteProductImport;
use App\Models\ProductImport;
use App\Services\Products\Import\ColumnMapping;
use App\Services\Products\Import\ParsedSpreadsheet;
use App\Services\Products\Import\ProductImportMapping;
use App\Services\Products\Import\ProductImportPreview;
use App\Services\Products\Import\ProductImportPreviewBuilder;
use App\Services\Products\Import\SpreadsheetParser;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ReviewProductImport extends Page
{
    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.resources.products.pages.review-product-import';

    public ProductImport $import;

    private ?ParsedSpreadsheet $spreadsheet = null;

    public function mount(string $record): void
    {
        $this->import = ProductImport::query()->with('company')->findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Revisão da importação';
    }

    public function getPreview(): ?ProductImportPreview
    {
        if ($this->import->status !== ProductImportStatus::AwaitingReview || ! is_array($this->import->mapping)) {
            return null;
        }

        return app(ProductImportPreviewBuilder::class)->build($this->import->company, $this->spreadsheet(), $this->currentMapping());
    }

    /**
     * @return array<int, ColumnMapping>
     */
    public function mappingColumns(): array
    {
        return $this->currentMapping()->columns;
    }

    public function groupingHeader(): ?string
    {
        return $this->currentMapping()->groupingHeader;
    }

    /**
     * Edits one spreadsheet column at a time in a modal — no bulk form, so
     * an admin who trusts the AI's mapping never has to open this at all.
     */
    public function editColumnAction(): Action
    {
        return Action::make('editColumn')
            ->label('Editar')
            ->iconButton()
            ->icon(Heroicon::OutlinedPencil)
            ->modalHeading(fn (array $arguments): string => 'Coluna "'.$this->argumentHeader($arguments).'"')
            ->modalDescription(function (array $arguments): ?string {
                $samples = $this->sampleValuesByHeader()[$this->argumentHeader($arguments)] ?? [];

                return $samples === [] ? null : 'Exemplos nessa coluna: '.implode(', ', $samples);
            })
            ->modalSubmitActionLabel('Salvar')
            ->fillForm(function (array $arguments): array {
                $column = $this->columnByHeader($this->argumentHeader($arguments));

                if ($column === null) {
                    return ['target' => 'ignore', 'field' => null, 'attribute_name' => null];
                }

                return [
                    'target' => $column->target,
                    'field' => in_array($column->target, ['product_field', 'variant_field'], true) ? $column->field : null,
                    'attribute_name' => $column->target === 'attribute' ? $column->field : null,
                ];
            })
            ->schema(ReviewProductImportForm::columnFields())
            ->action(function (array $arguments, array $data): void {
                $this->replaceColumn(ColumnMapping::validated(
                    header: $this->argumentHeader($arguments),
                    target: $data['target'] ?? null,
                    field: ($data['target'] ?? null) === 'attribute' ? ($data['attribute_name'] ?? null) : ($data['field'] ?? null),
                ));

                Notification::make()->title('Coluna atualizada')->success()->send();
            });
    }

    /**
     * Edits which column groups variations of the same product — kept as
     * its own tiny modal for the same reason: one decision, on demand.
     */
    public function editGroupingAction(): Action
    {
        return Action::make('editGrouping')
            ->label('Editar')
            ->iconButton()
            ->icon(Heroicon::OutlinedPencil)
            ->modalHeading('Variações do mesmo produto')
            ->modalSubmitActionLabel('Salvar')
            ->fillForm(fn (): array => ['grouping_header' => $this->currentMapping()->groupingHeader ?? ''])
            ->schema([
                Select::make('grouping_header')
                    ->hiddenLabel()
                    ->options(['' => 'Nenhuma — cada linha é um produto diferente'] + array_combine($this->spreadsheet()->headers, $this->spreadsheet()->headers))
                    ->native(false),
            ])
            ->action(function (array $data): void {
                $groupingHeader = is_string($data['grouping_header'] ?? null) && $data['grouping_header'] !== ''
                    ? $data['grouping_header']
                    : null;

                $this->import->update([
                    'mapping' => (new ProductImportMapping($groupingHeader, $this->currentMapping()->columns))->toArray(),
                ]);

                Notification::make()->title('Atualizado')->success()->send();
            });
    }

    public function confirm(): void
    {
        $this->import->update(['status' => ProductImportStatus::Importing]);

        ExecuteProductImport::dispatch($this->import);

        Notification::make()
            ->title('Importação iniciada')
            ->body('Os produtos estão sendo criados/atualizados no catálogo.')
            ->success()
            ->send();

        $this->redirect(ProductResource::getUrl('review-import', ['record' => $this->import]));
    }

    private function currentMapping(): ProductImportMapping
    {
        return ProductImportMapping::fromArray($this->import->mapping ?? []);
    }

    private function columnByHeader(string $header): ?ColumnMapping
    {
        return collect($this->mappingColumns())->first(fn (ColumnMapping $column): bool => $column->header === $header);
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    private function argumentHeader(array $arguments): string
    {
        return is_string($arguments['header'] ?? null) ? $arguments['header'] : '';
    }

    private function replaceColumn(ColumnMapping $updated): void
    {
        $mapping = $this->currentMapping();

        $columns = array_map(
            fn (ColumnMapping $column): ColumnMapping => $column->header === $updated->header ? $updated : $column,
            $mapping->columns,
        );

        $this->import->update([
            'mapping' => (new ProductImportMapping($mapping->groupingHeader, $columns))->toArray(),
        ]);
    }

    private function spreadsheet(): ParsedSpreadsheet
    {
        return $this->spreadsheet ??= app(SpreadsheetParser::class)->parse($this->import->spreadsheet_path, 'local');
    }

    /**
     * Up to 3 distinct sample values per header, shown in the edit modal so
     * the admin can see what a column actually contains.
     *
     * @return array<string, array<int, string>>
     */
    private function sampleValuesByHeader(): array
    {
        $spreadsheet = $this->spreadsheet();
        $samples = [];

        foreach ($spreadsheet->headers as $header) {
            $values = [];

            foreach ($spreadsheet->rows as $row) {
                $value = $row->cells[$header] ?? null;
                $value = is_scalar($value) ? trim((string) $value) : '';

                if ($value !== '' && ! in_array($value, $values, true)) {
                    $values[] = $value;
                }

                if (count($values) >= 3) {
                    break;
                }
            }

            $samples[$header] = $values;
        }

        return $samples;
    }
}
