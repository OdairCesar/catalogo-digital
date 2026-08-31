<?php

use App\Enums\ProductImportStatus;
use App\Jobs\MapProductImportColumns;
use App\Models\Company;
use App\Models\ProductImport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

function storeTestSpreadsheet(array $rows): string
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

test('maps spreadsheet columns via AI and leaves the import awaiting review', function () {
    $company = Company::factory()->create();

    $import = ProductImport::factory()->create([
        'company_id' => $company->id,
        'spreadsheet_path' => storeTestSpreadsheet([
            ['Nome do produto', 'Preço'],
            ['Camiseta', '39.90'],
        ]),
        'status' => ProductImportStatus::Pending,
    ]);

    OpenAI::fake([
        CreateResponse::fake([
            'model' => 'gpt-4.1',
            'choices' => [[
                'message' => ['content' => json_encode([
                    'grouping_header' => '',
                    'columns' => [
                        ['header' => 'Nome do produto', 'target' => 'product_field', 'field' => 'title'],
                        ['header' => 'Preço', 'target' => 'product_field', 'field' => 'base_price'],
                    ],
                ])],
            ]],
        ]),
    ]);

    MapProductImportColumns::dispatchSync($import);

    $import->refresh();

    expect($import->status)->toBe(ProductImportStatus::AwaitingReview);
    expect($import->mapping['columns'])->toHaveCount(2);
    expect($import->mapping['columns'][0])->toBe(['header' => 'Nome do produto', 'target' => 'product_field', 'field' => 'title']);
});

test('marks the import as failed when the spreadsheet has no readable header row', function () {
    $company = Company::factory()->create();

    $import = ProductImport::factory()->create([
        'company_id' => $company->id,
        'spreadsheet_path' => storeTestSpreadsheet([[null, null]]),
        'status' => ProductImportStatus::Pending,
    ]);

    MapProductImportColumns::dispatchSync($import);

    expect($import->fresh()->status)->toBe(ProductImportStatus::Failed);
    expect($import->fresh()->ai_error)->not->toBeNull();
});

test('marks the import as failed when the AI response is not valid json', function () {
    $company = Company::factory()->create();

    $import = ProductImport::factory()->create([
        'company_id' => $company->id,
        'spreadsheet_path' => storeTestSpreadsheet([
            ['Nome'],
            ['Camiseta'],
        ]),
        'status' => ProductImportStatus::Pending,
    ]);

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [['message' => ['content' => 'not json']]],
        ]),
    ]);

    MapProductImportColumns::dispatchSync($import);

    expect($import->fresh()->status)->toBe(ProductImportStatus::Failed);
});
