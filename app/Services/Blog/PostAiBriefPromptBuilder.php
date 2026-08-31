<?php

namespace App\Services\Blog;

use App\Enums\ContentStructure;
use App\Enums\ReaderInterest;
use App\Models\City;
use App\Services\Ai\JsonSchema;

final class PostAiBriefPromptBuilder
{
    /**
     * @return array{system: string, user: string}
     */
    public function build(PostAiBrief $brief, ?City $city): array
    {
        return [
            'system' => $this->systemPrompt(),
            'user' => $this->userPrompt($brief, $city),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            Você é uma redatora de conteúdo para o blog da Fit By Cae, uma marca de moda fitness que valoriza
            corpos reais e diversos — a proposta central é vender autoestima, não só peças de treino. Escreva
            com o tom de voz da marca: amigável, próximo, feminino e jovem, como uma amiga que entende de moda
            fitness conversando com a leitora, nunca uma marca falando de cima para baixo. Frases curtas,
            linguagem falada, sempre em 2ª pessoa ("você"), gírias leves e expressões afetivas usadas com
            naturalidade ("gata", "menina", "bora"), emojis com moderação. Evite jargão técnico e promessas
            irreais de corpo perfeito — reforce, direta ou indiretamente, que corpo real é bonito do jeito que é.
            Escreva sempre em português do Brasil, com HTML semântico simples (use apenas as tags <h2>, <h3>,
            <p>, <ul>, <ol>, <li>, <strong>, <em>, <a>, <table>, <thead>, <tbody>, <tr>, <th>, <td> — nunca <h1>,
            <script>, <style> ou atributos de estilo). Responda estritamente no formato JSON solicitado.
            PROMPT;
    }

    private function userPrompt(PostAiBrief $brief, ?City $city): string
    {
        $lines = [];

        $lines[] = $brief->title
            ? "Título sugerido: {$brief->title}"
            : "Assunto do artigo: {$brief->topic}";

        if ($brief->primaryKeyword) {
            $lines[] = "Palavra-chave principal: {$brief->primaryKeyword}";
        }

        if ($brief->secondaryKeywords !== []) {
            $lines[] = 'Palavras-chave secundárias: '.implode(', ', $brief->secondaryKeywords);
        }

        if ($brief->targetAudience) {
            $lines[] = "Público-alvo: {$brief->targetAudience}";
        }

        $lines[] = $brief->knowledgeLevel->promptInstruction();

        if ($brief->searchIntent) {
            $lines[] = "O leitor provavelmente pesquisou: \"{$brief->searchIntent}\"";
        }

        $lines[] = $brief->goal->promptInstruction();

        if ($brief->readerInterests !== []) {
            $interests = implode(', ', array_map(
                fn (ReaderInterest $interest): string => $interest->promptInstruction(),
                $brief->readerInterests,
            ));
            $lines[] = "O artigo deve deixar claro: {$interests}.";
        }

        $lines[] = $brief->brandPresence->promptInstruction();

        if ($city instanceof City) {
            $lines[] = "O artigo deve ser localizado para a cidade de {$city->name}/{$city->state?->uf}, mencionando o contexto local quando fizer sentido.";
        } else {
            $lines[] = 'O artigo deve ser genérico, sem foco em uma cidade específica.';
        }

        if ($brief->competitors) {
            $lines[] = "Referências/concorrentes a superar em qualidade: {$brief->competitors}";
        }

        $lines[] = $brief->length->promptInstruction();
        $lines[] = $brief->tone->promptInstruction();

        if ($brief->structure !== []) {
            $structure = implode(', ', array_map(
                fn (ContentStructure $structure): string => $structure->promptInstruction(),
                $brief->structure,
            ));
            $lines[] = "Estruture o conteúdo incluindo: {$structure}.";
        }

        $lines[] = 'Gere também um título (se não foi sugerido, crie um atrativo e otimizado para SEO), '
            .'um resumo curto (excerpt) de até 200 caracteres para ser usado em cards de listagem, '
            .'um meta_title de até 60 caracteres, uma meta_description de até 155 caracteres, '
            .'e uma lista de 3 a 6 tags relevantes para o artigo.';

        $lines[] = 'Gere também um image_prompt em inglês para a imagem de capa: descreva uma cena visual '
            .'concreta e específica, baseada literalmente no tema, na palavra-chave principal e no público-alvo '
            .'deste artigo (evite cenas genéricas de banco de imagens que serviriam para qualquer artigo). '
            .'Não inclua texto, letras, logotipos ou marcas d\'água na cena.';

        return implode("\n", $lines);
    }

    public function responseFormat(): JsonSchema
    {
        return new JsonSchema(
            name: 'blog_post',
            schema: [
                'type' => 'object',
                'required' => ['title', 'excerpt', 'content_html', 'meta_title', 'meta_description', 'tags', 'image_prompt'],
                'properties' => [
                    'title' => ['type' => 'string'],
                    'excerpt' => ['type' => 'string'],
                    'content_html' => ['type' => 'string'],
                    'meta_title' => ['type' => 'string'],
                    'meta_description' => ['type' => 'string'],
                    'tags' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                    'image_prompt' => ['type' => 'string'],
                ],
            ],
        );
    }
}
