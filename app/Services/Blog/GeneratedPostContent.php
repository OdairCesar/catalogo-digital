<?php

namespace App\Services\Blog;

final readonly class GeneratedPostContent
{
    /**
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public string $title,
        public string $excerpt,
        public string $contentHtml,
        public string $metaTitle,
        public string $metaDescription,
        public array $tags,
        public string $imagePrompt,
    ) {}
}
