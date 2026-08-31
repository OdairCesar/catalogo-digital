<?php

use App\Enums\ProductImportStatus;
use App\Jobs\ExecuteProductImport;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductImport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

function storeExecuteImportSpreadsheet(array $rows): string
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

test('writes the products to the catalog using the stored mapping and records the result', function () {
    $company = Company::factory()->create();

    $import = ProductImport::factory()->create([
        'company_id' => $company->id,
        'spreadsheet_path' => storeExecuteImportSpreadsheet([
            ['Nome', 'Preço'],
            ['Camiseta', '39.90'],
            ['Calça', '89.90'],
        ]),
        'status' => ProductImportStatus::AwaitingReview,
        'mapping' => [
            'grouping_header' => null,
            'columns' => [
                ['header' => 'Nome', 'target' => 'product_field', 'field' => 'title'],
                ['header' => 'Preço', 'target' => 'product_field', 'field' => 'base_price'],
            ],
        ],
    ]);

    ExecuteProductImport::dispatchSync($import);

    $import->refresh();

    expect($import->status)->toBe(ProductImportStatus::Completed);
    expect($import->result)->toBe(['created' => 2, 'updated' => 0, 'skipped' => 0, 'errors' => []]);
    expect(Product::where('company_id', $company->id)->count())->toBe(2);
});

test('fails the import when it has no mapping yet', function () {
    $company = Company::factory()->create();

    $import = ProductImport::factory()->create([
        'company_id' => $company->id,
        'mapping' => null,
    ]);

    ExecuteProductImport::dispatchSync($import);

    expect($import->fresh()->status)->toBe(ProductImportStatus::Failed);
});
