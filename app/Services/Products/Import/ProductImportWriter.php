<?php

namespace App\Services\Products\Import;

use App\Actions\Support\GenerateUniqueSlug;
use App\Enums\PageStatus;
use App\Enums\ProductAgeGroup;
use App\Enums\ProductCondition;
use App\Enums\ProductGender;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Persists a preview built by ProductImportPreviewBuilder: one product per
 * group, each in its own transaction so a single bad row doesn't abort the
 * whole batch — it's recorded as an error and the rest of the import proceeds.
 */
final class ProductImportWriter
{
    public function __construct(private readonly GenerateUniqueSlug $generateUniqueSlug) {}

    public function write(Company $company, ProductImportPreview $preview): ProductImportResult
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($preview->products as $previewProduct) {
            $title = trim($previewProduct->fields['title'] ?? '');

            if ($title === '') {
                $skipped++;

                continue;
            }

            try {
                $wasNew = $previewProduct->existingProductId === null;

                DB::transaction(function () use ($company, $previewProduct, $title): void {
                    $product = $previewProduct->existingProductId !== null
                        ? Product::query()->findOrFail($previewProduct->existingProductId)
                        : new Product(['company_id' => $company->id]);

                    $this->fillProduct($product, $previewProduct, $company, $title);
                    $product->save();

                    foreach ($previewProduct->variants as $previewVariant) {
                        if ($this->variantHasData($previewVariant)) {
                            $this->writeVariant($product, $previewVariant);
                        }
                    }
                });

                $wasNew ? $created++ : $updated++;
            } catch (Throwable $exception) {
                report($exception);
                $errors[] = "Linha {$this->firstRowNumber($previewProduct)} ({$title}): {$exception->getMessage()}";
                $skipped++;
            }
        }

        return new ProductImportResult($created, $updated, $skipped, $errors);
    }

    private function fillProduct(Product $product, PreviewProduct $preview, Company $company, string $title): void
    {
        $fields = $preview->fields;

        $attributes = [
            'company_id' => $company->id,
            'title' => $title,
            'description' => $this->nullableString($fields['description'] ?? ''),
            'brand' => $this->nullableString($fields['brand'] ?? ''),
            'sku' => $this->nullableString($fields['sku'] ?? ''),
            'gtin' => $this->nullableString($fields['gtin'] ?? ''),
            'mpn' => $this->nullableString($fields['mpn'] ?? ''),
            'condition' => $this->resolveEnum(ProductCondition::class, $fields['condition'] ?? '') ?? $product->condition ?? ProductCondition::New,
            'gender' => $this->resolveEnum(ProductGender::class, $fields['gender'] ?? '') ?? $product->gender,
            'age_group' => $this->resolveEnum(ProductAgeGroup::class, $fields['age_group'] ?? '') ?? $product->age_group,
            'base_price' => $this->parseDecimal($fields['base_price'] ?? '') ?? $product->base_price,
            'base_sale_price' => $this->parseDecimal($fields['base_sale_price'] ?? '') ?? $product->base_sale_price,
            'base_stock' => $this->parseInt($fields['base_stock'] ?? '') ?? $product->base_stock,
            'weight_kg' => $this->parseDecimal($fields['weight_kg'] ?? '') ?? $product->weight_kg,
            'height_cm' => $this->parseDecimal($fields['height_cm'] ?? '') ?? $product->height_cm,
            'width_cm' => $this->parseDecimal($fields['width_cm'] ?? '') ?? $product->width_cm,
            'length_cm' => $this->parseDecimal($fields['length_cm'] ?? '') ?? $product->length_cm,
        ];

        if (! $product->exists) {
            $attributes['slug'] = ($this->generateUniqueSlug)(Product::class, $title);
            $attributes['status'] = PageStatus::Draft;
        }

        if ($preview->category !== null) {
            $attributes['product_category_id'] = $this->resolveOrCreateCategoryId($preview->category);
        }

        $product->fill($attributes);
    }

    private function writeVariant(Product $product, PreviewVariant $previewVariant): void
    {
        $fields = $previewVariant->fields;
        $sku = $this->nullableString($fields['sku'] ?? '');

        $variant = $sku !== null
            ? ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereRaw('LOWER(sku) = ?', [Str::lower($sku)])
                ->first()
            : null;

        $variant ??= new ProductVariant(['product_id' => $product->id]);

        $variant->fill([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => $this->parseDecimal($fields['price'] ?? '') ?? $variant->price,
            'sale_price' => $this->parseDecimal($fields['sale_price'] ?? '') ?? $variant->sale_price,
            'stock' => $this->parseInt($fields['stock'] ?? '') ?? $variant->stock,
            'weight_kg' => $this->parseDecimal($fields['weight_kg'] ?? '') ?? $variant->weight_kg,
            'height_cm' => $this->parseDecimal($fields['height_cm'] ?? '') ?? $variant->height_cm,
            'width_cm' => $this->parseDecimal($fields['width_cm'] ?? '') ?? $variant->width_cm,
            'length_cm' => $this->parseDecimal($fields['length_cm'] ?? '') ?? $variant->length_cm,
            'is_active' => $variant->is_active ?? true,
        ]);
        $variant->save();

        if ($previewVariant->attributeValues !== []) {
            $ids = array_map(
                fn (AttributeValueResolution $resolution): int => $this->resolveOrCreateAttributeValueId($resolution),
                $previewVariant->attributeValues,
            );

            $variant->attributeValues()->sync($ids);
        }
    }

    private function variantHasData(PreviewVariant $variant): bool
    {
        return $variant->attributeValues !== [] || array_filter($variant->fields, fn (string $value): bool => $value !== '') !== [];
    }

    private function resolveOrCreateCategoryId(CategoryResolution $category): int
    {
        if ($category->existingCategoryId !== null) {
            return $category->existingCategoryId;
        }

        $name = trim($category->name);

        // firstOrCreate, not create: two different rows in the same import
        // can both resolve to the same not-yet-existing category, and by the
        // time the second one gets here the first one may have already
        // committed it in its own transaction.
        return ProductCategory::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        )->id;
    }

    private function resolveOrCreateAttributeValueId(AttributeValueResolution $resolution): int
    {
        if ($resolution->existingAttributeValueId !== null) {
            return $resolution->existingAttributeValueId;
        }

        $attributeId = ProductAttribute::query()->firstOrCreate(['name' => trim($resolution->attributeName)])->id;

        return ProductAttributeValue::query()->firstOrCreate([
            'product_attribute_id' => $attributeId,
            'value' => trim($resolution->value),
        ])->id;
    }

    private function firstRowNumber(PreviewProduct $product): int
    {
        return $product->variants[0]->sourceRowNumber ?? 0;
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * Parses a cell value as a decimal, accepting both "1234.56" and the
     * Brazilian "1.234,56" / "1234,56" formats commonly found in supplier
     * spreadsheets.
     */
    private function parseDecimal(string $raw): ?float
    {
        $value = preg_replace('/[^\d,.\-]/', '', trim($raw)) ?? '';

        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseInt(string $raw): ?int
    {
        $decimal = $this->parseDecimal($raw);

        return $decimal !== null ? (int) round($decimal) : null;
    }

    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T|null
     */
    private function resolveEnum(string $enum, string $raw): ?BackedEnum
    {
        $raw = trim($raw);

        if ($raw === '') {
            return null;
        }

        foreach ($enum::cases() as $case) {
            $matchesValue = Str::lower((string) $case->value) === Str::lower($raw);

            $label = method_exists($case, 'getLabel') ? $case->getLabel() : null;
            $matchesLabel = is_string($label) && Str::lower($label) === Str::lower($raw);

            if ($matchesValue || $matchesLabel) {
                return $case;
            }
        }

        return null;
    }
}
