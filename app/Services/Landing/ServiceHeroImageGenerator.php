<?php

namespace App\Services\Landing;

use App\Services\Ai\AiImageGenerator;

final class ServiceHeroImageGenerator
{
    public function __construct(private readonly AiImageGenerator $generator) {}

    /**
     * @param  array<int, string>  $benefits
     */
    public function generate(string $title, ?string $subtitle, ?string $description, array $benefits): string
    {
        return $this->generator->generate($this->buildPrompt($title, $subtitle, $description, $benefits), 'hero');
    }

    /**
     * @param  array<int, string>  $benefits
     */
    private function buildPrompt(string $title, ?string $subtitle, ?string $description, array $benefits): string
    {
        $parts = [
            "Hero illustration for a B2B technology service called \"{$title}\".",
        ];

        if (filled($subtitle)) {
            $parts[] = $subtitle;
        }

        if (filled($description)) {
            $parts[] = $description;
        }

        if ($benefits !== []) {
            $parts[] = 'Key benefits to convey visually: '.implode(', ', $benefits).'.';
        }

        $parts[] = 'Wide hero image with visual breathing room on one side for overlaid text, no embedded text or logos in the image itself.';

        return implode(' ', $parts);
    }
}
