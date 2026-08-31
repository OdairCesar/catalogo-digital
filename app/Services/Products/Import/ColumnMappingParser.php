<?php

namespace App\Services\Products\Import;

use App\Services\Ai\ValidatesJsonPayload;

/**
 * Validates the AI's raw column-mapping response against the actual
 * spreadsheet headers, so a malformed or partial AI response can never
 * produce a mapping with missing/invalid/duplicated columns.
 */
final class ColumnMappingParser
{
    use ValidatesJsonPayload;

    public function parse(string $jsonPayload, ParsedSpreadsheet $spreadsheet): ProductImportMapping
    {
        $data = $this->decodeJsonObject($jsonPayload);

        $proposedByHeader = collect($this->requireArray($data, 'columns'))
            ->filter(fn (mixed $column): bool => is_array($column) && is_string($column['header'] ?? null))
            ->keyBy(fn (array $column): string => $column['header']);

        $columns = array_map(
            function (string $header) use ($proposedByHeader): ColumnMapping {
                $proposed = $proposedByHeader->get($header, []);

                return ColumnMapping::validated(
                    header: $header,
                    target: $proposed['target'] ?? null,
                    field: $proposed['field'] ?? null,
                );
            },
            $spreadsheet->headers,
        );

        $groupingHeader = $data['grouping_header'] ?? null;
        $groupingHeader = is_string($groupingHeader) && in_array($groupingHeader, $spreadsheet->headers, true)
            ? $groupingHeader
            : null;

        return new ProductImportMapping($groupingHeader, $columns);
    }
}
