<?php

namespace App\Services\Portfolio;

use App\Services\Ai\AiImageGenerator;

final class PortfolioCoverImageGenerator
{
    public function __construct(private readonly AiImageGenerator $generator) {}

    public function generate(string $title, ?string $excerpt, ?string $serviceTitle): string
    {
        return $this->generator->generate($this->buildPrompt($title, $excerpt, $serviceTitle), 'portfolio');
    }

    private function buildPrompt(string $title, ?string $excerpt, ?string $serviceTitle): string
    {
        $parts = [
            "Cover image for a portfolio case study called \"{$title}\".",
        ];

        if (filled($serviceTitle)) {
            $parts[] = "Service delivered: {$serviceTitle}.";
        }

        if (filled($excerpt)) {
            $parts[] = $excerpt;
        }

        $parts[] = 'Wide cover image with visual breathing room on one side for overlaid text, no embedded text or logos in the image itself.';

        return implode(' ', $parts);
    }
}
