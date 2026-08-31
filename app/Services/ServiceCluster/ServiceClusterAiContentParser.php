<?php

namespace App\Services\ServiceCluster;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\ValidatesJsonPayload;

final class ServiceClusterAiContentParser
{
    use ValidatesJsonPayload;

    public function parse(string $jsonPayload): GeneratedServiceClusterContent
    {
        $data = json_decode($jsonPayload, associative: true);

        if (! is_array($data)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return new GeneratedServiceClusterContent(
            title: $this->requireString($data, 'title'),
            subtitle: $this->requireString($data, 'subtitle'),
            description: $this->requireString($data, 'description'),
            benefits: $this->requireStringArray($data, 'benefits'),
            faq: array_values(array_map($this->requireFaqItem(...), $this->requireArray($data, 'faq'))),
            keywords: $this->requireStringArray($data, 'keywords'),
            metaTitle: $this->requireString($data, 'meta_title'),
            metaDescription: $this->requireString($data, 'meta_description'),
            imagePrompt: $this->requireString($data, 'image_prompt'),
        );
    }

    /**
     * @return array{question: string, answer: string}
     */
    private function requireFaqItem(mixed $value): array
    {
        if (! is_array($value)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return [
            'question' => $this->requireString($value, 'question'),
            'answer' => $this->requireString($value, 'answer'),
        ];
    }
}
