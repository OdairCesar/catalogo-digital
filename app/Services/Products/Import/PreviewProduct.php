<?php

namespace App\Services\Products\Import;

final readonly class PreviewProduct
{
    /**
     * @param  array<string, string>  $fields  product field key => raw cell value
     * @param  array<int, PreviewVariant>  $variants
     * @param  array<int, string>  $warnings
     */
    public function __construct(
        public ?int $existingProductId,
        public array $fields,
        public ?CategoryResolution $category,
        public array $variants,
        public array $warnings,
    ) {}
}
