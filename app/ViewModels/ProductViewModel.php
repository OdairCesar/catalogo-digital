<?php

namespace App\ViewModels;

final readonly class ProductViewModel
{
    /**
     * @param  array<int, string>  $galleryImageUrls
     * @param  array<int, array{label: string, priceLabel: ?string, stockLabel: string, imageUrl: ?string, inStock: bool}>  $variants
     * @param  list<array{label: string}>  $sizeOptions
     * @param  list<array{label: string, hex: string, imageUrl: ?string}>  $colorOptions
     * @param  array<string, array{priceLabel: ?string, stockLabel: string, imageUrl: ?string, inStock: bool}>  $variantMatrix
     * @param  list<array{title: string, url: string, coverImageUrl: ?string, priceLabel: ?string}>  $relatedProducts
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public ?float $price,
        public ?string $priceLabel,
        public ?string $salePriceLabel,
        public ?string $installmentLabel,
        public string $stockLabel,
        public string $availability,
        public ?string $coverImageUrl,
        public array $galleryImageUrls,
        public ?string $brand,
        public string $conditionLabel,
        public ?string $categoryName,
        public array $variants,
        public array $sizeOptions,
        public array $colorOptions,
        public array $variantMatrix,
        public array $relatedProducts,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $jsonLd = [],
    ) {}
}
