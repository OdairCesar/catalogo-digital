<?php

use App\Enums\PageStatus;
use App\Enums\ProductAgeGroup;
use App\Enums\ProductCondition;
use App\Enums\ProductGender;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the products resource index page renders', function () {
    Product::factory()->count(2)->create();

    $this->get('/admin/products')->assertOk();
});

test('creating a product through the resource form works end to end', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Básica',
            'slug' => 'camiseta-basica',
            'sku' => 'CAM-001',
            'description' => 'Camiseta 100% algodão.',
            'condition' => ProductCondition::New->value,
            'base_price' => 49.9,
            'base_stock' => 10,
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::where('slug', 'camiseta-basica')->exists())->toBeTrue();
});

test('creating a product with an existing category links it correctly', function () {
    $company = Company::factory()->create();
    $category = ProductCategory::factory()->create(['google_product_category' => '1604']);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Básica',
            'slug' => 'camiseta-basica',
            'product_category_id' => $category->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'camiseta-basica')->firstOrFail();

    expect($product->category->google_product_category)->toBe('1604');
});

test('creating a product with gender and age group persists them correctly', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Feminina',
            'slug' => 'camiseta-feminina',
            'gender' => ProductGender::Female->value,
            'age_group' => ProductAgeGroup::Adult->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'camiseta-feminina')->firstOrFail();

    expect($product->gender)->toBe(ProductGender::Female)
        ->and($product->age_group)->toBe(ProductAgeGroup::Adult);
});

test('a gtin with an invalid number of digits fails validation', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Básica',
            'slug' => 'camiseta-basica',
            'gtin' => '12345',
        ])
        ->call('create')
        ->assertHasFormErrors(['gtin']);
});

test('a valid 13 digit gtin passes validation', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Básica',
            'slug' => 'camiseta-basica',
            'gtin' => '7891234567895',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('the masked price field strips thousand separators before saving', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Básica',
            'slug' => 'camiseta-basica',
            'base_price' => '1,234.56',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect((float) Product::where('slug', 'camiseta-basica')->first()->base_price)->toBe(1234.56);
});

test('a product cover image is uploaded to the cloudinary disk', function () {
    Storage::fake('cloudinary');

    $company = Company::factory()->create();

    $path = Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta Básica',
            'slug' => 'camiseta-basica',
            'cover_image' => UploadedFile::fake()->image('camiseta.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->get('data.cover_image');

    expect($path)->not->toBeNull();

    Storage::disk('cloudinary')->assertExists($path);
});

test('the feed warning shows when a new product has no brand and no cover image', function () {
    Livewire::test(CreateProduct::class)
        ->assertSee('não vai aparecer no feed do Google Shopping')
        ->assertSee('sem marca')
        ->assertSee('sem imagem principal');
});

test('the feed warning only lists the pieces that are still missing', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm(['brand' => 'OD Wear'])
        ->assertSee('sem imagem principal')
        ->assertDontSee('sem marca');
});

test('the feed warning disappears once brand and cover image are both set', function () {
    Storage::fake('cloudinary');

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'brand' => 'OD Wear',
            'cover_image' => UploadedFile::fake()->image('camiseta.jpg'),
        ])
        ->assertDontSee('não vai aparecer no feed do Google Shopping');
});

test('the toggle publish status action publishes and unpublishes a product', function () {
    $product = Product::factory()->create(['status' => PageStatus::Draft]);

    Livewire::test(ListProducts::class)
        ->callTableAction('togglePublishStatus', $product);

    expect($product->refresh()->status)->toBe(PageStatus::Published);

    Livewire::test(ListProducts::class)
        ->callTableAction('togglePublishStatus', $product);

    expect($product->refresh()->status)->toBe(PageStatus::Draft);
});

test('products can be bulk published and unpublished', function () {
    $products = Product::factory()->count(2)->create(['status' => PageStatus::Draft]);

    Livewire::test(ListProducts::class)
        ->callTableBulkAction('publish', $products);

    expect($products->fresh()->pluck('status')->all())->toBe([PageStatus::Published, PageStatus::Published]);

    Livewire::test(ListProducts::class)
        ->callTableBulkAction('unpublish', $products);

    expect($products->fresh()->pluck('status')->all())->toBe([PageStatus::Draft, PageStatus::Draft]);
});

test('the products list can be filtered by company', function () {
    $companyA = Company::factory()->create(['name' => 'OD Tec']);
    $companyB = Company::factory()->create(['name' => 'Outra Empresa']);

    $productA = Product::factory()->create(['company_id' => $companyA->id]);
    $productB = Product::factory()->create(['company_id' => $companyB->id]);

    Livewire::test(ListProducts::class)
        ->assertCanSeeTableRecords([$productA, $productB])
        ->filterTable('company_id', $companyA->id)
        ->assertCanSeeTableRecords([$productA])
        ->assertCanNotSeeTableRecords([$productB]);
});
