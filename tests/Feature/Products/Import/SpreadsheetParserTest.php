<?php

use App\Services\Products\Import\SpreadsheetParser;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

function writeTestSpreadsheet(array $rows): string
{
    $export = new class($rows) implements FromArray
    {
        public function __construct(private readonly array $rows) {}

        public function array(): array
        {
            return $this->rows;
        }
    };

    $path = 'test-spreadsheets/'.Str::uuid().'.xlsx';

    Excel::store($export, $path, 'local');

    return $path;
}

test('parses headers and rows from the first non-empty sheet', function () {
    $path = writeTestSpreadsheet([
        ['Nome do produto', 'Preço', 'Cor'],
        ['Camiseta', '39.90', 'Azul'],
        ['Calça', '89.90', 'Preto'],
    ]);

    $spreadsheet = app(SpreadsheetParser::class)->parse($path, 'local');

    expect($spreadsheet->headers)->toBe(['Nome do produto', 'Preço', 'Cor']);
    expect($spreadsheet->rows)->toHaveCount(2);
    expect($spreadsheet->rows[0]->cells)->toBe([
        'Nome do produto' => 'Camiseta',
        'Preço' => 39.9,
        'Cor' => 'Azul',
    ]);
    expect($spreadsheet->rows[1]->rowNumber)->toBe(2);
});

test('skips blank leading rows and blank trailing rows', function () {
    $path = writeTestSpreadsheet([
        [null, null, null],
        ['Nome', 'SKU'],
        ['Camiseta', 'CAM-1'],
        [null, null],
    ]);

    $spreadsheet = app(SpreadsheetParser::class)->parse($path, 'local');

    expect($spreadsheet->headers)->toBe(['Nome', 'SKU']);
    expect($spreadsheet->rows)->toHaveCount(1);
    expect($spreadsheet->rows[0]->cells)->toBe(['Nome' => 'Camiseta', 'SKU' => 'CAM-1']);
});

test('returns an empty spreadsheet when every row is blank', function () {
    $path = writeTestSpreadsheet([
        [null, null],
        [null, null],
    ]);

    $spreadsheet = app(SpreadsheetParser::class)->parse($path, 'local');

    expect($spreadsheet->headers)->toBe([]);
    expect($spreadsheet->rows)->toBe([]);
});
