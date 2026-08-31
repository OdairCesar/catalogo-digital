<?php

namespace App\Services\ServiceCluster;

use App\Models\Service;
use App\Services\Ai\JsonSchema;

final class ServiceClusterAiBriefPromptBuilder
{
    /**
     * @return array{system: string, user: string}
     */
    public function build(ServiceClusterAiBrief $brief, Service $service): array
    {
        return [
            'system' => $this->systemPrompt(),
            'user' => $this->userPrompt($brief, $service),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            Você é um redator especialista em SEO e marketing de conteúdo, escrevendo para o site da OD Tec,
            uma empresa de tecnologia. Escreva sempre em português do Brasil. Responda estritamente no formato
            JSON solicitado, sem markdown.
            PROMPT;
    }

    private function userPrompt(ServiceClusterAiBrief $brief, Service $service): string
    {
        $lines = [];

        $lines[] = "A OD Tec presta o serviço \"{$service->title}\": {$service->description}";

        if ($service->benefits !== []) {
            $lines[] = 'Benefícios já destacados para esse serviço: '.implode(', ', $service->benefits).'.';
        }

        $lines[] = "Crie uma página de sub-tópico (cluster temático) desse serviço, focada especificamente em: \"{$brief->topic}\".";
        $lines[] = 'A página deve ter identidade própria, sem repetir literalmente o texto do serviço principal, '
            .'mas conectada tematicamente a ele para reforçar a relevância de SEO do domínio.';
        $lines[] = 'Gere um title (H1, até 60 caracteres), um subtitle (uma frase de efeito), uma description '
            .'(2 a 3 parágrafos), uma lista de 4 a 6 benefits específicos deste sub-tópico, uma lista de 2 a 4 '
            .'perguntas frequentes (faq) com question/answer, e uma lista de 3 a 6 keywords de cauda longa.';
        $lines[] = 'Você pode usar os tokens {cidade}, {uf} e {regiao} no texto para localização automática por '
            .'cidade, seguindo o mesmo padrão já usado nos textos de serviços da OD Tec.';
        $lines[] = 'Gere também um meta_title (até 60 caracteres), uma meta_description (até 155 caracteres) e '
            .'um image_prompt em inglês para uma imagem de capa: cena visual concreta e específica baseada no '
            .'sub-tópico, sem texto, letras, logotipos ou marcas d\'água.';

        return implode("\n", $lines);
    }

    public function responseFormat(): JsonSchema
    {
        return new JsonSchema(
            name: 'service_cluster',
            schema: [
                'type' => 'object',
                'required' => ['title', 'subtitle', 'description', 'benefits', 'faq', 'keywords', 'meta_title', 'meta_description', 'image_prompt'],
                'properties' => [
                    'title' => ['type' => 'string'],
                    'subtitle' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'benefits' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                    'faq' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'required' => ['question', 'answer'],
                            'properties' => [
                                'question' => ['type' => 'string'],
                                'answer' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'keywords' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                    'meta_title' => ['type' => 'string'],
                    'meta_description' => ['type' => 'string'],
                    'image_prompt' => ['type' => 'string'],
                ],
            ],
        );
    }
}
