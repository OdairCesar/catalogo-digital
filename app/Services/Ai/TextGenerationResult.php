<?php

namespace App\Services\Ai;

final readonly class TextGenerationResult
{
    public function __construct(
        public string $content,
        public string $model,
    ) {}
}
