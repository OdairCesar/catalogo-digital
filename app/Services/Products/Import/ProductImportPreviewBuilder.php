<?php

namespace App\Services\Products\Import;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Deterministic (no AI) step that applies a column mapping to every parsed
 * row: groups rows into products/variants, and resolves category and
 * attribute-value text against what already exists in the catalog so the
 * review screen can show exactly what will be created vs. reused. Runs
 * synchronously — cheap enough to re-run whenever the admin tweaks the
 * mapping, without going through the AI job again.
 */
final class ProductImportPreviewBuilder
{
    public function build(Company $company, ParsedSpreadsheet $spreadsheet, ProductImportMapping $mapping): ProductImportPreview
    {
        $columnsByHeader = collect($mapping->columns)->keyBy(fn (ColumnMapping $column): string => $column->header);

        $warnings = [];

        if ($columnsByHeader->contains(fn (ColumnMapping $column): bool => $column->target === 'product_field' && $column->field === 'title') === false) {
            $warnings[] = 'Nenhuma coluna foi mapeada para o título do produto. Ajuste o mapeamento antes de importar.';
        }

        $products = array_map(
            fn (array $rows): PreviewProduct => $this->buildProduct($company, $rows, $columnsByHeader),
            $this->groupRows($spreadsheet->rows, $mapping->groupingHeader),
        );

        return new ProductImportPreview($products, $warnings);
    }

    /**
     * @param  array<int, ParsedRow>  $rows
     * @return array<int, array<int, ParsedRow>>
     */
    private function groupRows(array $rows, ?string $groupingHeader): array
    {
        if ($groupingHeader === null) {
            return array_map(fn (ParsedRow $row): array => [$row], $rows);
        }

        $groups = [];

        foreach ($rows as $row) {
            $key = $this->cellToString($row->cells[$groupingHeader] ?? null);
            $key = $key !== '' ? $key : 'row-'.$row->rowNumber;
            $groups[$key][] = $row;
        }

        return array_values($groups);
    }

    /**
     * @param  array<int, ParsedRow>  $rows
     * @param  Collection<string, ColumnMapping>  $columnsByHeader
     */
    private function buildProduct(Company $company, array $rows, Collection $columnsByHeader): PreviewProduct
    {
        $firstRow = $rows[0];
        $fields = $this->extractFields($firstRow, $columnsByHeader, 'product_field');

        $category = ($fields['category'] ?? '') !== ''
            ? $this->resolveCategory($fields['category'])
            : null;

        $existingProductId = $this->resolveExistingProduct($company, $fields['sku'] ?? '', $fields['title'] ?? '');

        $variants = array_map(
            fn (ParsedRow $row): PreviewVariant => $this->buildVariant($row, $columnsByHeader),
            $rows,
        );

        $warnings = [];

        if (($fields['title'] ?? '') === '') {
            $warnings[] = 'Linha sem título — este produto não será importado.';
        }

        return new PreviewProduct($existingProductId, $fields, $category, $variants, $warnings);
    }

    /**
     * @param  Collection<string, ColumnMapping>  $columnsByHeader
     */
    private function buildVariant(ParsedRow $row, Collection $columnsByHeader): PreviewVariant
    {
        $fields = $this->extractFields($row, $columnsByHeader, 'variant_field');

        $attributeValues = $columnsByHeader
            ->filter(fn (ColumnMapping $column): bool => $column->target === 'attribute')
            ->map(function (ColumnMapping $column) use ($row): ?AttributeValueResolution {
                $value = $this->cellToString($row->cells[$column->header] ?? null);

                return $value !== '' ? $this->resolveAttributeValue($column->field, $value) : null;
            })
            ->filter()
            ->values()
            ->all();

        return new PreviewVariant($row->rowNumber, $fields, $attributeValues);
    }

    /**
     * @param  Collection<string, ColumnMapping>  $columnsByHeader
     * @return array<string, string>
     */
    private function extractFields(ParsedRow $row, Collection $columnsByHeader, string $target): array
    {
        $fields = [];

        foreach ($columnsByHeader as $header => $column) {
            if ($column->target !== $target) {
                continue;
            }

            $fields[$column->field] = $this->cellToString($row->cells[$header] ?? null);
        }

        return $fields;
    }

    private function resolveCategory(string $name): CategoryResolution
    {
        $existingId = ProductCategory::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower(trim($name))])
            ->value('id');

        return new CategoryResolution($name, is_numeric($existingId) ? (int) $existingId : null);
    }

    private function resolveAttributeValue(string $attributeName, string $value): AttributeValueResolution
    {
        $existingId = ProductAttributeValue::query()
            ->whereHas('attribute', fn ($query) => $query->whereRaw('LOWER(name) = ?', [Str::lower(trim($attributeName))]))
            ->whereRaw('LOWER(value) = ?', [Str::lower(trim($value))])
            ->value('id');

        return new AttributeValueResolution($attributeName, $value, is_numeric($existingId) ? (int) $existingId : null);
    }

    private function resolveExistingProduct(Company $company, string $sku, string $title): ?int
    {
        if ($sku !== '') {
            $id = Product::query()
                ->where('company_id', $company->id)
                ->whereRaw('LOWER(sku) = ?', [Str::lower(trim($sku))])
                ->value('id');

            if (is_numeric($id)) {
                return (int) $id;
            }
        }

        if ($title !== '') {
            $id = Product::query()
                ->where('company_id', $company->id)
                ->whereRaw('LOWER(title) = ?', [Str::lower(trim($title))])
                ->value('id');

            if (is_numeric($id)) {
                return (int) $id;
            }
        }

        return null;
    }

    private function cellToString(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_scalar($value) => trim((string) $value),
            default => '',
        };
    }
}
