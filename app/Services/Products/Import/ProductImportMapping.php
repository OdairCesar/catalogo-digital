<?php

namespace App\Services\Products\Import;

final readonly class ProductImportMapping
{
    /** @var array<int, string> */
    public const array PRODUCT_FIELDS = [
        'title', 'description', 'brand', 'sku', 'gtin', 'mpn', 'condition',
        'gender', 'age_group', 'category', 'base_price', 'base_sale_price',
        'base_stock', 'weight_kg', 'height_cm', 'width_cm', 'length_cm',
    ];

    /** @var array<int, string> */
    public const array VARIANT_FIELDS = [
        'sku', 'price', 'sale_price', 'stock', 'weight_kg', 'height_cm', 'width_cm', 'length_cm',
    ];

    /**
     * @param  array<int, ColumnMapping>  $columns
     */
    public function __construct(
        public ?string $groupingHeader,
        public array $columns,
    ) {}

    /**
     * @return array{grouping_header: ?string, columns: array<int, array{header: string, target: string, field: string}>}
     */
    public function toArray(): array
    {
        return [
            'grouping_header' => $this->groupingHeader,
            'columns' => array_map(fn (ColumnMapping $column): array => $column->toArray(), $this->columns),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $groupingHeader = $data['grouping_header'] ?? null;
        $columns = is_array($data['columns'] ?? null) ? $data['columns'] : [];

        return new self(
            groupingHeader: is_string($groupingHeader) && $groupingHeader !== '' ? $groupingHeader : null,
            columns: array_values(array_map(
                fn (mixed $column): ColumnMapping => ColumnMapping::fromArray(is_array($column) ? $column : []),
                $columns,
            )),
        );
    }
}
