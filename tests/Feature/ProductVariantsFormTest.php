<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('creating a product with variants persists them through the repeater', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta',
            'slug' => 'camiseta',
            'variants' => [
                ['sku' => 'CAM-P', 'price' => 39.9, 'stock' => 5],
                ['sku' => 'CAM-M', 'price' => 39.9, 'stock' => 8],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'camiseta')->firstOrFail();

    expect($product->variants()->count())->toBe(2);
    expect($product->variants()->orderBy('sku')->pluck('sku')->all())->toBe(['CAM-M', 'CAM-P']);
});

test('creating a product with a variant attaches the selected attribute values', function () {
    $company = Company::factory()->create();
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $size = ProductAttribute::factory()->create(['name' => 'Tamanho']);
    $medium = ProductAttributeValue::factory()->create(['product_attribute_id' => $size->id, 'value' => 'M']);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta',
            'slug' => 'camiseta',
            'variants' => [
                ['sku' => 'CAM-AZUL-M', 'attributeValues' => [$blue->id, $medium->id]],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $variant = Product::where('slug', 'camiseta')->firstOrFail()->variants()->firstOrFail();

    expect($variant->attributeValues()->orderBy('value')->pluck('value')->all())->toBe(['Azul', 'M']);
});

test('the variant repeater item label shows the attribute values, not the sku', function () {
    $company = Company::factory()->create();
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $size = ProductAttribute::factory()->create(['name' => 'Tamanho']);
    $medium = ProductAttributeValue::factory()->create(['product_attribute_id' => $size->id, 'value' => 'M']);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta',
            'slug' => 'camiseta',
            'variants' => [
                ['sku' => 'CAM-AZUL-M', 'attributeValues' => [$blue->id, $medium->id]],
            ],
        ])
        ->assertSee('Cor: Azul, Tamanho: M')
        ->assertDontSee('CAM-AZUL-M');
});

test('creating a product with multiple variants without any sku is valid', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta',
            'slug' => 'camiseta-sem-sku',
            'variants' => [
                ['sku' => null, 'price' => 39.9],
                ['sku' => null, 'price' => 45.9],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'camiseta-sem-sku')->firstOrFail();

    expect($product->variants()->count())->toBe(2);
    expect($product->variants()->pluck('sku')->filter()->all())->toBe([]);
});

test('creating a product with two variants sharing the same attribute combination fails validation', function () {
    $company = Company::factory()->create();
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Camiseta',
            'slug' => 'camiseta-duplicada',
            'variants' => [
                ['sku' => null, 'attributeValues' => [$blue->id]],
                ['sku' => null, 'attributeValues' => [$blue->id]],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['variants']);
});

test('creating a product without any variant is a valid simple product', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Produto Simples',
            'slug' => 'produto-simples',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'produto-simples')->firstOrFail();

    expect($product->variants()->count())->toBe(0);
});

test('creating a product with an availability window persists it through the repeater', function () {
    $company = Company::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'company_id' => $company->id,
            'title' => 'Marmita do dia',
            'slug' => 'marmita-do-dia',
            'availabilities' => [
                ['weekday' => 1, 'starts_at' => '11:00', 'ends_at' => '14:00'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'marmita-do-dia')->firstOrFail();

    expect($product->availabilities()->count())->toBe(1);
    expect($product->availabilities()->first()->weekday)->toBe(1);
});
