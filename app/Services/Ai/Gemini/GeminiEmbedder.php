<?php

namespace App\Services\Ai\Gemini;

use App\Services\Ai\Embedder;
use Gemini\Contracts\ClientContract;
use Gemini\Data\ContentEmbedding;
use Gemini\Laravel\Facades\Gemini;
use RuntimeException;

final class GeminiEmbedder implements Embedder
{
    public function embed(array $inputs): array
    {
        if ($inputs === []) {
            return [];
        }

        // The Gemini facade's @method docblock mistypes embeddingModel() as
        // returning GenerativeModel instead of EmbeddingModalContract, so the
        // client is resolved through the facade root instead of a static call.
        $client = Gemini::getFacadeRoot();

        if (! $client instanceof ClientContract) {
            throw new RuntimeException('The Gemini facade root is not a Gemini client.');
        }

        $response = $client->embeddingModel(config()->string('services.gemini.embedding_model'))
            ->batchEmbedContents(...array_values($inputs));

        return array_values(array_map(
            fn (ContentEmbedding $embedding): array => array_values($embedding->values),
            $response->embeddings,
        ));
    }
}
