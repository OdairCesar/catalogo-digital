<?php

namespace App\Services\Products;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Expands a flat list of selected attribute-value ids (e.g. "Cor: Azul",
 * "Cor: Vermelho", "Tamanho: M", "Tamanho: G") into the cartesian product of
 * one combination per attribute, so a whole variant grade can be generated
 * from a single form instead of adding each row by hand.
 */
final class VariantGridGenerator
{
    /**
     * @param  array<int, int|string>  $attributeValueIds
     * @param  array<array-key, mixed>  $existingVariants  keyed by repeater item id
     * @return array<array-key, mixed>
     */
    public function generate(array $attributeValueIds, array $existingVariants = []): array
    {
        if ($attributeValueIds === []) {
            return $existingVariants;
        }

        $groups = ProductAttributeValue::query()
            ->whereIn('id', $attributeValueIds)
            ->get()
            ->groupBy('product_attribute_id')
            ->map(fn ($group): array => $group->all())
            ->values()
            ->all();

        $combinations = Arr::crossJoin(...$groups);

        $existingCombos = collect($existingVariants)
            ->map(function (mixed $row): array {
                $attributeValues = is_array($row) && is_array($row['attributeValues'] ?? null) ? $row['attributeValues'] : [];

                return collect($attributeValues)
                    ->filter(fn (mixed $id): bool => is_numeric($id))
                    ->map(fn (mixed $id): int => (int) $id)
                    ->sort()
                    ->values()
                    ->all();
            })
            ->all();

        foreach ($combinations as $combo) {
            $ids = collect($combo)->pluck('id')->filter(fn (mixed $id): bool => is_numeric($id))->map(fn (mixed $id): int => (int) $id)->sort()->values()->all();

            if (in_array($ids, $existingCombos, true)) {
                continue;
            }

            $existingVariants[(string) Str::uuid()] = [
                'sku' => null,
                'attributeValues' => $ids,
                'price' => null,
                'sale_price' => null,
                'stock' => null,
                'weight_kg' => null,
                'height_cm' => null,
                'width_cm' => null,
                'length_cm' => null,
                'image' => null,
                'is_active' => true,
            ];

            $existingCombos[] = $ids;
        }

        return $existingVariants;
    }

    /**
     * Same as generate(), but the attributes and values don't need to exist
     * yet — each one is created on the fly (or reused if the name/value
     * already matches), so a brand new catalog with nothing registered can
     * still generate a full grade from a single form.
     *
     * @param  array<array-key, mixed>  $attributeGroups
     * @param  array<array-key, mixed>  $existingVariants
     * @return array<array-key, mixed>
     */
    public function generateFromAttributeGroups(array $attributeGroups, array $existingVariants = []): array
    {
        $normalizedGroups = [];

        foreach ($attributeGroups as $rawGroup) {
            if (! is_array($rawGroup)) {
                continue;
            }

            $rawName = $rawGroup['name'] ?? null;
            $name = trim(is_scalar($rawName) ? (string) $rawName : '');

            $rawValues = is_array($rawGroup['values'] ?? null) ? $rawGroup['values'] : [];
            $values = collect($rawValues)
                ->map(fn (mixed $value): string => trim(is_scalar($value) ? (string) $value : ''))
                ->filter(fn (string $value): bool => $value !== '')
                ->unique()
                ->values()
                ->all();

            if ($name === '' || $values === []) {
                continue;
            }

            $normalizedGroups[] = ['name' => $name, 'values' => $values];
        }

        $groups = collect($normalizedGroups);

        if ($groups->isEmpty()) {
            return $this->generate([], $existingVariants);
        }

        $now = now();
        $names = $groups->map(fn ($group): string => $group['name'])->unique()->values();

        ProductAttribute::query()->upsert(
            $names->map(fn (string $name): array => ['name' => $name, 'created_at' => $now, 'updated_at' => $now])->all(),
            uniqueBy: ['name'],
        );

        $attributeIdsByName = ProductAttribute::query()->whereIn('name', $names)->pluck('id', 'name');

        $valueRows = $groups
            ->flatMap(function ($group) use ($attributeIdsByName) {
                $attributeId = $attributeIdsByName->get($group['name']);

                if (! is_numeric($attributeId)) {
                    throw new RuntimeException("Attribute \"{$group['name']}\" was not found after being upserted.");
                }

                return collect($group['values'])
                    ->map(fn (string $value) => [
                        'product_attribute_id' => (int) $attributeId,
                        'value' => $value,
                    ])
                    ->all();
            })
            ->all();

        ProductAttributeValue::query()->upsert(
            collect($valueRows)->map(fn (array $row): array => [...$row, 'created_at' => $now, 'updated_at' => $now])->all(),
            uniqueBy: ['product_attribute_id', 'value'],
        );

        $idsByKey = ProductAttributeValue::query()
            ->whereIn('product_attribute_id', $attributeIdsByName->values())
            ->whereIn('value', $groups->map(fn ($group): array => $group['values'])->flatten()->unique())
            ->get(['id', 'product_attribute_id', 'value'])
            ->mapWithKeys(fn (ProductAttributeValue $value): array => ["{$value->product_attribute_id}|{$value->value}" => $value->id]);

        $valueIds = collect($valueRows)
            ->map(fn ($row): ?int => $idsByKey["{$row['product_attribute_id']}|{$row['value']}"] ?? null)
            ->filter()
            ->values()
            ->all();

        return $this->generate($valueIds, $existingVariants);
    }
}
