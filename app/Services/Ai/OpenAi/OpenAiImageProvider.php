<?php

namespace App\Services\Ai\OpenAi;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\ImageProvider;
use OpenAI\Laravel\Facades\OpenAI;

final class OpenAiImageProvider implements ImageProvider
{
    public function generateImageBytes(string $prompt): string
    {
        $response = OpenAI::images()->create([
            'model' => config('services.openai.image_model'),
            'prompt' => $prompt,
            'n' => 1,
            'size' => config('services.openai.image_size'),
            'quality' => config('services.openai.image_quality'),
        ]);

        $base64 = $response->data[0]->b64_json ?? null;

        if (! is_string($base64) || $base64 === '') {
            throw AiGenerationException::invalidImageResponse();
        }

        $contents = base64_decode($base64, strict: true);

        if ($contents === false) {
            throw AiGenerationException::invalidImageResponse();
        }

        return $contents;
    }
}
