<?php

namespace App\Services\Ai;

interface ImageProvider
{
    /**
     * Generates an image for the given prompt and returns the raw image bytes.
     */
    public function generateImageBytes(string $prompt): string;
}
