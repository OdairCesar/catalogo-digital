<?php

namespace App\ViewModels;

final readonly class ServiceViewModel
{
    /**
     * @param  array<int, string>  $benefits
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array{label: string, url: string}>  $relatedLinks
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $title,
        public string $subtitle,
        public string $description,
        public array $benefits,
        public array $faq,
        public SeoMeta $seo,
        public ?string $heroImageUrl = null,
        public array $breadcrumbs = [],
        public array $relatedLinks = [],
        public array $jsonLd = [],
    ) {}
}
