<?php

namespace App\ViewModels;

final readonly class PortfolioItemViewModel
{
    /**
     * @param  array<int, array<string, mixed>>  $relatedItems
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $title,
        public string $excerpt,
        public ?string $content,
        public ?string $coverImageUrl,
        public ?string $externalUrl,
        public ?string $serviceName,
        public ?string $serviceUrl,
        public array $relatedItems,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $jsonLd = [],
    ) {}
}
