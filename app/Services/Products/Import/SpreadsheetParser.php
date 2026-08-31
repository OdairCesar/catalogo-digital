<?php

namespace App\Services\Products\Import;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reads an XLSX file with an unknown, arbitrary column layout into a plain
 * header + rows structure, without assuming a fixed schema up front: the
 * first non-empty row is treated as the header row, whatever its columns are.
 */
final class SpreadsheetParser
{
    public function parse(string $path, ?string $disk = null): ParsedSpreadsheet
    {
        $sheets = Excel::toCollection(null, $path, $disk);

        /** @var Collection<int, Collection<array-key, mixed>>|null $sheet */
        $sheet = $sheets->first(fn (Collection $rows): bool => $rows->isNotEmpty());

        if ($sheet === null) {
            return new ParsedSpreadsheet([], []);
        }

        $rows = $sheet->values();

        $headerIndex = $rows->search(fn (Collection $row): bool => $row->contains(fn (mixed $cell): bool => filled($cell)));

        if ($headerIndex === false) {
            return new ParsedSpreadsheet([], []);
        }

        $headerRow = $rows->get($headerIndex);

        if ($headerRow === null) {
            return new ParsedSpreadsheet([], []);
        }

        $headers = $headerRow
            ->map(fn (mixed $cell, int $index): string => $this->normalizeHeader($cell, $index))
            ->values()
            ->all();

        $parsedRows = $rows->slice($headerIndex + 1)
            ->filter(fn (Collection $row): bool => $row->contains(fn (mixed $cell): bool => filled($cell)))
            ->values()
            ->map(fn (Collection $row, int $index): ParsedRow => new ParsedRow(
                rowNumber: $index + 1,
                cells: $this->mapCells($headers, $row),
            ))
            ->all();

        return new ParsedSpreadsheet($headers, $parsedRows);
    }

    private function normalizeHeader(mixed $cell, int $index): string
    {
        $label = is_scalar($cell) ? trim((string) $cell) : '';

        return $label !== '' ? $label : "coluna_{$index}";
    }

    /**
     * @param  array<int, string>  $headers
     * @param  Collection<array-key, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapCells(array $headers, Collection $row): array
    {
        $cells = [];

        foreach ($headers as $index => $header) {
            $cells[$header] = $row->get($index);
        }

        return $cells;
    }
}
