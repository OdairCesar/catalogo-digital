<?php

namespace App\Services\Products\Import;

use App\Services\Ai\JsonSchema;

final class ColumnMappingPromptBuilder
{
    private const int SAMPLE_ROWS = 5;

    /**
     * @param  array<string, array<int, CatalogTerm>>  $catalogContext  keyed by spreadsheet header
     * @return array{system: string, user: string}
     */
    public function build(ParsedSpreadsheet $spreadsheet, array $catalogContext): array
    {
        return [
            'system' => $this->systemPrompt(),
            'user' => $this->userPrompt($spreadsheet, $catalogContext),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            Você é um assistente especialista em importação de catálogos de produtos para e-commerce.
            Você recebe o cabeçalho e uma amostra de linhas de uma planilha enviada por um fornecedor ou
            lojista, com layout arbitrário (os nomes das colunas variam de planilha para planilha), e deve
            propor um mapeamento de cada coluna para o catálogo interno.

            Cada coluna deve ser classificada em um destes alvos:
            - "product_field": a coluna descreve um campo do produto principal (ex: título, marca, categoria).
            - "variant_field": a coluna descreve um campo específico da variação/SKU daquela linha (ex: preço,
              estoque, SKU da variação).
            - "attribute": a coluna representa um atributo de variação (ex: uma coluna "Cor" com valores como
              "Azul", "Vermelho"). Nesse caso "field" é o nome do atributo — reaproveite um nome já existente
              no catálogo quando fizer sentido (veja "Atributos/categorias existentes" abaixo) em vez de criar
              um nome novo equivalente.
            - "ignore": a coluna não é relevante para a importação (ex: comentários internos, colunas vazias).

            Quando várias linhas da planilha pertencem ao mesmo produto (variações diferentes de um mesmo
            item), tente identificar qual coluna agrupa essas linhas (ex: um "SKU pai" ou o próprio nome do
            produto repetido) e retorne o nome dessa coluna em "grouping_header". Se não houver esse padrão
            (cada linha é um produto independente, sem variações), retorne "grouping_header" como string vazia.

            Responda estritamente no formato JSON solicitado, com uma entrada em "columns" para CADA coluna
            do cabeçalho recebido, na mesma ordem.
            PROMPT;
    }

    /**
     * @param  array<string, array<int, CatalogTerm>>  $catalogContext
     */
    private function userPrompt(ParsedSpreadsheet $spreadsheet, array $catalogContext): string
    {
        $lines = [];

        $lines[] = 'Cabeçalho da planilha: '.implode(' | ', $spreadsheet->headers);

        $lines[] = "\nAmostra de linhas:";
        foreach (array_slice($spreadsheet->rows, 0, self::SAMPLE_ROWS) as $row) {
            $values = array_map(
                fn (string $header): string => $this->cellToString($row->cells[$header] ?? null),
                $spreadsheet->headers,
            );
            $lines[] = implode(' | ', $values);
        }

        $catalogLines = $this->catalogContextLines($catalogContext);

        if ($catalogLines !== []) {
            $lines[] = "\nAtributos/categorias existentes no catálogo, mais parecidos com cada coluna (prefira reutilizá-los):";
            array_push($lines, ...$catalogLines);
        }

        $lines[] = "\nCampos de produto disponíveis: ".implode(', ', ProductImportMapping::PRODUCT_FIELDS);
        $lines[] = 'Campos de variação disponíveis: '.implode(', ', ProductImportMapping::VARIANT_FIELDS);

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, array<int, CatalogTerm>>  $catalogContext
     * @return array<int, string>
     */
    private function catalogContextLines(array $catalogContext): array
    {
        $lines = [];

        foreach ($catalogContext as $header => $terms) {
            if ($terms === []) {
                continue;
            }

            $labels = implode(', ', array_map(fn (CatalogTerm $term): string => "[{$term->type}] {$term->label}", $terms));
            $lines[] = "- \"{$header}\": {$labels}";
        }

        return $lines;
    }

    private function cellToString(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_scalar($value) => (string) $value,
            default => '',
        };
    }

    public function responseFormat(): JsonSchema
    {
        return new JsonSchema(
            name: 'product_import_column_mapping',
            schema: [
                'type' => 'object',
                'required' => ['grouping_header', 'columns'],
                'properties' => [
                    'grouping_header' => ['type' => 'string'],
                    'columns' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'required' => ['header', 'target', 'field'],
                            'properties' => [
                                'header' => ['type' => 'string'],
                                'target' => [
                                    'type' => 'string',
                                    'enum' => ['product_field', 'variant_field', 'attribute', 'ignore'],
                                ],
                                'field' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
}
