<?php

namespace App\Services\ServiceCluster;

final readonly class GeneratedServiceClusterContent
{
    /**
     * @param  array<int, string>  $benefits
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @param  array<int, string>  $keywords
     */
    public function __construct(
        public string $title,
        public string $subtitle,
        public string $description,
        public array $benefits,
        public array $faq,
        public array $keywords,
        public string $metaTitle,
        public string $metaDescription,
        public string $imagePrompt,
    ) {}
}
