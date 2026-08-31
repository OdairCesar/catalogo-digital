<?php

namespace App\ViewModels;

final readonly class StateViewModel
{
    /**
     * @param  array<int, array{name: string, uf: string, intro: string, url: string}>  $cities
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $name,
        public string $uf,
        public string $intro,
        public string $businessText,
        public array $cities,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $jsonLd = [],
    ) {}
}
