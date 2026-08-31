<?php

namespace App\ViewModels;

use Illuminate\Support\Carbon;

final readonly class BlogPostViewModel
{
    /**
     * @param  array<int, string>  $tags
     * @param  array<int, array<string, mixed>>  $relatedPosts
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $title,
        public string $excerpt,
        public string $contentHtml,
        public ?string $coverImageUrl,
        public ?Carbon $publishedAt,
        public ?string $categoryLabel,
        public ?string $categoryUrl,
        public array $tags,
        public ?string $authorName,
        public array $relatedPosts,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $jsonLd = [],
    ) {}
}
