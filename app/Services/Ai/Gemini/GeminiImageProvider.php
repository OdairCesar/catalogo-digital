<?php

namespace App\Services\Ai\Gemini;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\ImageProvider;
use Gemini\Data\GenerationConfig;
use Gemini\Data\ImageConfig;
use Gemini\Laravel\Facades\Gemini;
use ValueError;

final class GeminiImageProvider implements ImageProvider
{
    public function generateImageBytes(string $prompt): string
    {
        $response = Gemini::generativeModel(model: config()->string('services.gemini.image_model'))
            ->withGenerationConfig(new GenerationConfig(
                imageConfig: new ImageConfig(aspectRatio: config()->string('services.gemini.image_aspect_ratio')),
            ))
            ->generateContent($prompt);

        try {
            $parts = $response->parts();
        } catch (ValueError) {
            throw AiGenerationException::invalidImageResponse();
        }

        foreach ($parts as $part) {
            if ($part->inlineData === null || $part->inlineData->data === '') {
                continue;
            }

            $contents = base64_decode($part->inlineData->data, strict: true);

            if ($contents !== false) {
                return $contents;
            }
        }

        throw AiGenerationException::invalidImageResponse();
    }
}
