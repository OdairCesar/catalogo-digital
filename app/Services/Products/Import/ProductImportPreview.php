<?php

namespace App\Services\Products\Import;

final readonly class ProductImportPreview
{
    /**
     * @param  array<int, PreviewProduct>  $products
     * @param  array<int, string>  $warnings  spreadsheet-level warnings, not tied to a single product
     */
    public function __construct(
        public array $products,
        public array $warnings,
    ) {}
}
