<?php

namespace App\ViewModels;

final readonly class CityViewModel
{
    /**
     * @param  array<int, array{label: string, subtitle: string, icon: ?string, url: string}>  $landingPages
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $name,
        public string $uf,
        public string $region,
        public string $intro,
        public string $businessText,
        public array $landingPages,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $jsonLd = [],
    ) {}
}
