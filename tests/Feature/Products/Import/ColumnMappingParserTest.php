<?php

use App\Services\Products\Import\ColumnMappingParser;
use App\Services\Products\Import\ParsedSpreadsheet;

function parsedSpreadsheetWithHeaders(array $headers): ParsedSpreadsheet
{
    return new ParsedSpreadsheet($headers, []);
}

test('builds one column mapping per spreadsheet header, in order', function () {
    $spreadsheet = parsedSpreadsheetWithHeaders(['Nome', 'Preço', 'Cor']);

    $payload = json_encode([
        'grouping_header' => '',
        'columns' => [
            ['header' => 'Preço', 'target' => 'product_field', 'field' => 'base_price'],
            ['header' => 'Nome', 'target' => 'product_field', 'field' => 'title'],
            ['header' => 'Cor', 'target' => 'attribute', 'field' => 'Cor'],
        ],
    ]);

    $mapping = app(ColumnMappingParser::class)->parse($payload, $spreadsheet);

    expect($mapping->columns)->toHaveCount(3);
    expect($mapping->columns[0]->header)->toBe('Nome');
    expect($mapping->columns[0]->target)->toBe('product_field');
    expect($mapping->columns[1]->header)->toBe('Preço');
    expect($mapping->columns[2]->target)->toBe('attribute');
    expect($mapping->columns[2]->field)->toBe('Cor');
});

test('downgrades a column with an invalid field for its target to ignore', function () {
    $spreadsheet = parsedSpreadsheetWithHeaders(['Nome']);

    $payload = json_encode([
        'grouping_header' => '',
        'columns' => [
            ['header' => 'Nome', 'target' => 'product_field', 'field' => 'not_a_real_field'],
        ],
    ]);

    $mapping = app(ColumnMappingParser::class)->parse($payload, $spreadsheet);

    expect($mapping->columns[0]->target)->toBe('ignore');
    expect($mapping->columns[0]->field)->toBe('');
});

test('defaults a header missing from the AI response to ignore', function () {
    $spreadsheet = parsedSpreadsheetWithHeaders(['Nome', 'Observações internas']);

    $payload = json_encode([
        'grouping_header' => '',
        'columns' => [
            ['header' => 'Nome', 'target' => 'product_field', 'field' => 'title'],
        ],
    ]);

    $mapping = app(ColumnMappingParser::class)->parse($payload, $spreadsheet);

    expect($mapping->columns[1]->header)->toBe('Observações internas');
    expect($mapping->columns[1]->target)->toBe('ignore');
});

test('only accepts a grouping_header that matches an actual spreadsheet header', function () {
    $spreadsheet = parsedSpreadsheetWithHeaders(['SKU pai', 'Nome']);

    $payload = json_encode([
        'grouping_header' => 'Coluna inexistente',
        'columns' => [],
    ]);

    $mapping = app(ColumnMappingParser::class)->parse($payload, $spreadsheet);

    expect($mapping->groupingHeader)->toBeNull();
});
