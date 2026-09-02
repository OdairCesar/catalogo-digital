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
     * @param  list<array{text: ?string, initial: string, name: ?string, detail: ?string, extra_fields: array<int, array{label: string, value: string}>}>  $testimonials
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
        public array $testimonials,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $jsonLd = [],
    ) {}
}
