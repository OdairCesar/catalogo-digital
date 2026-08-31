<?php

use App\Filament\Resources\ProductInventories\Pages\CreateProductInventory;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the product inventories resource index page renders', function () {
    ProductInventory::factory()->create();

    $this->get('/admin/product-inventories')->assertOk();
});

test('creating a product level inventory override works end to end', function () {
    $company = Company::factory()->create();
    $store = Store::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id]);

    Livewire::test(CreateProductInventory::class)
        ->fillForm([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'price' => 19.9,
            'quantity' => 3,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ProductInventory::where('store_id', $store->id)->where('product_id', $product->id)->exists())->toBeTrue();
});

test('creating a variant level inventory override works end to end', function () {
    $company = Company::factory()->create();
    $store = Store::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Livewire::test(CreateProductInventory::class)
        ->fillForm([
            'store_id' => $store->id,
            'product_variant_id' => $variant->id,
            'price' => 24.9,
            'quantity' => 7,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ProductInventory::where('store_id', $store->id)->where('product_variant_id', $variant->id)->exists())->toBeTrue();
});

test('creating an inventory without selecting a product or a variant fails validation', function () {
    $store = Store::factory()->create();

    Livewire::test(CreateProductInventory::class)
        ->fillForm([
            'store_id' => $store->id,
            'price' => 19.9,
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('creating a duplicate product level inventory override for the same store fails validation', function () {
    $company = Company::factory()->create();
    $store = Store::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id]);

    ProductInventory::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
    ]);

    Livewire::test(CreateProductInventory::class)
        ->fillForm([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'price' => 19.9,
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('selecting a product that belongs to a different company than the chosen store is rejected', function () {
    $store = Store::factory()->create();
    $productFromAnotherCompany = Product::factory()->create();

    Livewire::test(CreateProductInventory::class)
        ->fillForm([
            'store_id' => $store->id,
            'product_id' => $productFromAnotherCompany->id,
            'price' => 19.9,
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('selecting a variant whose product belongs to a different company than the chosen store is rejected', function () {
    $store = Store::factory()->create();
    $productFromAnotherCompany = Product::factory()->create();
    $variantFromAnotherCompany = ProductVariant::factory()->create(['product_id' => $productFromAnotherCompany->id]);

    Livewire::test(CreateProductInventory::class)
        ->fillForm([
            'store_id' => $store->id,
            'product_variant_id' => $variantFromAnotherCompany->id,
            'price' => 19.9,
        ])
        ->call('create')
        ->assertHasFormErrors(['product_variant_id']);
});
