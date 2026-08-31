<?php

use App\Enums\ProductImportStatus;
use App\Filament\Resources\Products\Pages\ImportProducts;
use App\Filament\Resources\Products\Pages\ReviewProductImport;
use App\Jobs\ExecuteProductImport;
use App\Jobs\MapProductImportColumns;
use App\Models\Company;
use App\Models\ProductImport;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

function storeReviewImportSpreadsheet(array $rows): string
{
    $export = new class($rows) implements FromArray
    {
        public function __construct(private readonly array $rows) {}

        public function array(): array
        {
            return $this->rows;
        }
    };

    $path = 'product-imports/'.Str::uuid().'.xlsx';
    Excel::store($export, $path, 'local');

    return $path;
}

test('uploading a spreadsheet creates a pending import and dispatches the mapping job', function () {
    Storage::fake('local');
    Bus::fake();

    $company = Company::factory()->create();

    Livewire::test(ImportProducts::class)
        ->fillForm([
            'company_id' => $company->id,
            'spreadsheet' => UploadedFile::fake()->create(
                'produtos.xlsx',
                10,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ),
        ])
        ->call('analyze');

    $import = ProductImport::query()->firstOrFail();

    expect($import->company_id)->toBe($company->id);
    expect($import->status)->toBe(ProductImportStatus::Pending);

    Bus::assertDispatched(MapProductImportColumns::class, fn (MapProductImportColumns $job): bool => $job->import->is($import));
});

test('the review page renders the mapping while awaiting review', function () {
    $import = ProductImport::factory()->awaitingReview()->create([
        'spreadsheet_path' => storeReviewImportSpreadsheet([
            ['Nome do produto'],
            ['Camiseta'],
        ]),
        'mapping' => [
            'grouping_header' => null,
            'columns' => [
                ['header' => 'Nome do produto', 'target' => 'product_field', 'field' => 'title'],
            ],
        ],
    ]);

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('Nome do produto');
});

test('the review page renders the pending state', function () {
    $import = ProductImport::factory()->create(['status' => ProductImportStatus::Pending]);

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($import->company->name);
});

test('the review page renders the failed state with the error message', function () {
    $import = ProductImport::factory()->failed()->create();

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($import->ai_error);
});

test('the review page renders the completed state with the result summary', function () {
    $import = ProductImport::factory()->completed()->create([
        'result' => ['created' => 3, 'updated' => 1, 'skipped' => 0, 'errors' => ['Linha 5: SKU duplicado']],
    ]);

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('3 criado(s)')
        ->assertSee('Linha 5: SKU duplicado');
});

test('editing a single column via its modal action updates only that column', function () {
    $import = ProductImport::factory()->awaitingReview()->create([
        'spreadsheet_path' => storeReviewImportSpreadsheet([
            ['Nome do produto', 'Cor'],
            ['Camiseta', 'Azul'],
        ]),
        'mapping' => [
            'grouping_header' => null,
            'columns' => [
                ['header' => 'Nome do produto', 'target' => 'product_field', 'field' => 'title'],
                ['header' => 'Cor', 'target' => 'ignore', 'field' => ''],
            ],
        ],
    ]);

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->callAction(
            TestAction::make('editColumn')->arguments(['header' => 'Cor']),
            data: ['target' => 'attribute', 'attribute_name' => 'Cor'],
        );

    $columns = collect($import->fresh()->mapping['columns']);

    expect($columns->firstWhere('header', 'Cor'))->toBe(['header' => 'Cor', 'target' => 'attribute', 'field' => 'Cor']);
    expect($columns->firstWhere('header', 'Nome do produto')['target'])->toBe('product_field');
});

test('editing the grouping column via its modal action updates it', function () {
    $import = ProductImport::factory()->awaitingReview()->create([
        'spreadsheet_path' => storeReviewImportSpreadsheet([
            ['SKU pai', 'Nome'],
            ['P1', 'Camiseta'],
        ]),
        'mapping' => [
            'grouping_header' => null,
            'columns' => [
                ['header' => 'SKU pai', 'target' => 'ignore', 'field' => ''],
                ['header' => 'Nome', 'target' => 'product_field', 'field' => 'title'],
            ],
        ],
    ]);

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->callAction('editGrouping', data: ['grouping_header' => 'SKU pai']);

    expect($import->fresh()->mapping['grouping_header'])->toBe('SKU pai');
});

test('confirming the review saves the mapping and dispatches the execute job', function () {
    Bus::fake();

    $import = ProductImport::factory()->awaitingReview()->create([
        'spreadsheet_path' => storeReviewImportSpreadsheet([
            ['Nome do produto'],
            ['Camiseta'],
        ]),
        'mapping' => [
            'grouping_header' => null,
            'columns' => [
                ['header' => 'Nome do produto', 'target' => 'product_field', 'field' => 'title'],
            ],
        ],
    ]);

    Livewire::test(ReviewProductImport::class, ['record' => $import->getRouteKey()])
        ->call('confirm');

    expect($import->fresh()->status)->toBe(ProductImportStatus::Importing);

    Bus::assertDispatched(ExecuteProductImport::class, fn (ExecuteProductImport $job): bool => $job->import->is($import));
});
