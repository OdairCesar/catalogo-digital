<?php

namespace App\Services\Products\Import;

/**
 * How a single spreadsheet column should be interpreted.
 *
 * - target=product_field: $field is one of ProductImportMapping::PRODUCT_FIELDS
 * - target=variant_field: $field is one of ProductImportMapping::VARIANT_FIELDS
 * - target=attribute: $field is the attribute name (e.g. "Cor"), reused from
 *   the catalog when it already exists, otherwise created on import
 * - target=ignore: $field is ''
 */
final readonly class ColumnMapping
{
    public function __construct(
        public string $header,
        public string $target,
        public string $field,
    ) {}

    /**
     * @return array{header: string, target: string, field: string}
     */
    public function toArray(): array
    {
        return [
            'header' => $this->header,
            'target' => $this->target,
            'field' => $this->field,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return self::validated(
            header: $data['header'] ?? null,
            target: $data['target'] ?? null,
            field: $data['field'] ?? null,
        );
    }

    /**
     * Builds a mapping from raw, untrusted input (an AI response or admin-edited
     * form state), downgrading anything inconsistent — an unknown target, or a
     * field that doesn't belong to that target — to "ignore" instead of throwing.
     */
    public static function validated(mixed $header, mixed $target, mixed $field): self
    {
        $header = is_string($header) ? $header : '';
        $field = is_string($field) ? trim($field) : '';

        if (! is_string($target) || ! in_array($target, ['product_field', 'variant_field', 'attribute', 'ignore'], true)) {
            return new self($header, 'ignore', '');
        }

        $isValid = match ($target) {
            'product_field' => in_array($field, ProductImportMapping::PRODUCT_FIELDS, true),
            'variant_field' => in_array($field, ProductImportMapping::VARIANT_FIELDS, true),
            'attribute' => $field !== '',
            'ignore' => true,
        };

        return $isValid
            ? new self($header, $target, $target === 'ignore' ? '' : $field)
            : new self($header, 'ignore', '');
    }
}
