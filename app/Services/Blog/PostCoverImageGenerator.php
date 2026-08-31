<?php

namespace App\Services\Blog;

use App\Enums\ImageStyle;
use App\Services\Ai\AiImageGenerator;

final class PostCoverImageGenerator
{
    public function __construct(private readonly AiImageGenerator $generator) {}

    public function generate(string $imagePrompt, ImageStyle $style): string
    {
        $prompt = implode(' ', [$imagePrompt, $style->promptInstruction()]);

        return $this->generator->generate($prompt, 'cover');
    }
}
