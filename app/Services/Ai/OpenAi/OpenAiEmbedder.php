<?php

namespace App\Services\Ai\OpenAi;

use App\Services\Ai\Embedder;
use OpenAI\Laravel\Facades\OpenAI;

final class OpenAiEmbedder implements Embedder
{
    public function embed(array $inputs): array
    {
        if ($inputs === []) {
            return [];
        }

        $response = OpenAI::embeddings()->create([
            'model' => config()->string('services.openai.embedding_model'),
            'input' => $inputs,
        ]);

        return array_map(
            fn ($embedding): array => $embedding->embedding,
            $response->embeddings,
        );
    }
}
