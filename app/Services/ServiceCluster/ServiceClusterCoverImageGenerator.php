<?php

namespace App\Services\ServiceCluster;

use App\Services\Ai\AiImageGenerator;

final class ServiceClusterCoverImageGenerator
{
    public function __construct(private readonly AiImageGenerator $generator) {}

    public function generate(string $imagePrompt): string
    {
        return $this->generator->generate($imagePrompt, 'cluster-hero');
    }
}
