<?php

namespace App\Services\Products\Import;

final readonly class PreviewVariant
{
    /**
     * @param  array<string, string>  $fields  variant field key => raw cell value
     * @param  array<int, AttributeValueResolution>  $attributeValues
     */
    public function __construct(
        public int $sourceRowNumber,
        public array $fields,
        public array $attributeValues,
    ) {}
}
