<?php

namespace App\Services\ServiceCluster;

use App\Exceptions\AiGenerationException;
use App\Models\Service;
use App\Services\Ai\JsonSchema;
use App\Services\Ai\TextGenerator;

final class ServiceClusterHeroImageGenerator
{
    public function __construct(
        private readonly TextGenerator $textGenerator,
        private readonly ServiceClusterCoverImageGenerator $coverImageGenerator,
    ) {}

    /**
     * @param  array<int, string>  $benefits
     */
    public function generate(Service $service, string $title, ?string $subtitle, ?string $description, array $benefits): string
    {
        $result = $this->textGenerator->generate(
            [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->userPrompt($service, $title, $subtitle, $description, $benefits)],
            ],
            $this->responseFormat(),
            0.7,
        );

        $imagePrompt = $this->parseImagePrompt($result->content);

        return $this->coverImageGenerator->generate($imagePrompt);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            Você é um diretor de arte que escreve prompts de imagem em inglês para um gerador de imagens de IA.
            Responda estritamente no formato JSON solicitado, sem markdown.
            PROMPT;
    }

    /**
     * @param  array<int, string>  $benefits
     */
    private function userPrompt(Service $service, string $title, ?string $subtitle, ?string $description, array $benefits): string
    {
        $lines = [];

        $lines[] = "A OD Tec presta o serviço \"{$service->title}\": {$service->description}";
        $lines[] = 'Esta é uma página de sub-tópico (cluster temático) desse serviço, com o seguinte conteúdo:';
        $lines[] = "Título: {$title}";

        if (filled($subtitle)) {
            $lines[] = "Subtítulo: {$subtitle}";
        }

        if (filled($description)) {
            $lines[] = "Descrição: {$description}";
        }

        if ($benefits !== []) {
            $lines[] = 'Benefícios: '.implode(', ', $benefits).'.';
        }

        $lines[] = 'Gere um image_prompt em inglês para uma imagem de capa: cena visual concreta e específica '
            .'baseada nesse conteúdo, sem texto, letras, logotipos ou marcas d\'água.';

        return implode("\n", $lines);
    }

    private function responseFormat(): JsonSchema
    {
        return new JsonSchema(
            name: 'service_cluster_hero_image',
            schema: [
                'type' => 'object',
                'required' => ['image_prompt'],
                'properties' => [
                    'image_prompt' => ['type' => 'string'],
                ],
            ],
        );
    }

    private function parseImagePrompt(string $jsonPayload): string
    {
        $data = json_decode($jsonPayload, associative: true);

        if (! is_array($data) || ! is_string($data['image_prompt'] ?? null) || $data['image_prompt'] === '') {
            throw AiGenerationException::invalidResponseShape();
        }

        return $data['image_prompt'];
    }
}
