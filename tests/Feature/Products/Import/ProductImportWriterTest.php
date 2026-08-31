<?php

use App\Enums\ProductCondition;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Services\Products\Import\AttributeValueResolution;
use App\Services\Products\Import\CategoryResolution;
use App\Services\Products\Import\PreviewProduct;
use App\Services\Products\Import\PreviewVariant;
use App\Services\Products\Import\ProductImportPreview;
use App\Services\Products\Import\ProductImportWriter;

test('creates a new product with variants and attribute values', function () {
    $company = Company::factory()->create();

    $preview = new ProductImportPreview([
        new PreviewProduct(
            existingProductId: null,
            fields: ['title' => 'Camiseta', 'brand' => 'Acme', 'base_price' => '39,90', 'condition' => 'novo'],
            category: null,
            variants: [
                new PreviewVariant(1, ['sku' => 'CAM-AZUL', 'price' => '39,90', 'stock' => '10'], [
                    new AttributeValueResolution('Cor', 'Azul', null),
                ]),
            ],
            warnings: [],
        ),
    ], []);

    $result = app(ProductImportWriter::class)->write($company, $preview);

    expect($result->created)->toBe(1)
        ->and($result->updated)->toBe(0)
        ->and($result->skipped)->toBe(0)
        ->and($result->errors)->toBe([]);

    $product = Product::where('company_id', $company->id)->where('title', 'Camiseta')->firstOrFail();

    expect($product->brand)->toBe('Acme');
    expect($product->condition)->toBe(ProductCondition::New);
    expect((float) $product->base_price)->toBe(39.9);
    expect($product->slug)->not->toBeEmpty();

    $variant = $product->variants()->firstOrFail();
    expect($variant->sku)->toBe('CAM-AZUL');
    expect((float) $variant->stock)->toBe(10.0);
    expect($variant->attributeValues()->first()->value)->toBe('Azul');

    $attribute = ProductAttribute::where('name', 'Cor')->first();
    expect($attribute)->not->toBeNull();
});

test('updates an existing product instead of creating a duplicate', function () {
    $company = Company::factory()->create();
    $existing = Product::factory()->create(['company_id' => $company->id, 'sku' => 'CAM-1', 'title' => 'Nome antigo']);

    $preview = new ProductImportPreview([
        new PreviewProduct(
            existingProductId: $existing->id,
            fields: ['title' => 'Nome atualizado', 'sku' => 'CAM-1'],
            category: null,
            variants: [],
            warnings: [],
        ),
    ], []);

    $result = app(ProductImportWriter::class)->write($company, $preview);

    expect($result->created)->toBe(0)->and($result->updated)->toBe(1);
    expect($existing->fresh()->title)->toBe('Nome atualizado');
    expect(Product::where('company_id', $company->id)->count())->toBe(1);
});

test('reuses an existing category and attribute value instead of duplicating them', function () {
    $company = Company::factory()->create();
    $category = ProductCategory::factory()->create(['name' => 'Roupas']);
    $attribute = ProductAttribute::factory()->create(['name' => 'Cor']);
    $value = ProductAttributeValue::factory()->create(['product_attribute_id' => $attribute->id, 'value' => 'Azul']);

    $preview = new ProductImportPreview([
        new PreviewProduct(
            existingProductId: null,
            fields: ['title' => 'Camiseta'],
            category: new CategoryResolution('Roupas', $category->id),
            variants: [
                new PreviewVariant(1, [], [new AttributeValueResolution('Cor', 'Azul', $value->id)]),
            ],
            warnings: [],
        ),
    ], []);

    app(ProductImportWriter::class)->write($company, $preview);

    expect(ProductCategory::count())->toBe(1);
    expect(ProductAttributeValue::count())->toBe(1);

    $product = Product::where('company_id', $company->id)->firstOrFail();
    expect($product->product_category_id)->toBe($category->id);
});

test('skips a product without a title and records it as skipped', function () {
    $company = Company::factory()->create();

    $preview = new ProductImportPreview([
        new PreviewProduct(null, ['title' => ''], null, [], ['Linha sem título']),
    ], []);

    $result = app(ProductImportWriter::class)->write($company, $preview);

    expect($result->skipped)->toBe(1)
        ->and($result->created)->toBe(0)
        ->and(Product::count())->toBe(0);
});

test('creates only one category when two products in the same batch need the same new one', function () {
    $company = Company::factory()->create();

    $preview = new ProductImportPreview([
        new PreviewProduct(null, ['title' => 'Camiseta'], new CategoryResolution('Roupas', null), [], []),
        new PreviewProduct(null, ['title' => 'Calça'], new CategoryResolution('Roupas', null), [], []),
    ], []);

    $result = app(ProductImportWriter::class)->write($company, $preview);

    expect($result->created)->toBe(2)->and($result->errors)->toBe([]);
    expect(ProductCategory::where('name', 'Roupas')->count())->toBe(1);

    $categoryId = ProductCategory::where('name', 'Roupas')->value('id');
    expect(Product::where('company_id', $company->id)->pluck('product_category_id')->unique()->all())->toBe([$categoryId]);
});

test('does not create a variant when the row has no variant fields or attributes', function () {
    $company = Company::factory()->create();

    $preview = new ProductImportPreview([
        new PreviewProduct(
            existingProductId: null,
            fields: ['title' => 'Produto simples', 'base_price' => '10.00'],
            category: null,
            variants: [
                new PreviewVariant(1, ['sku' => '', 'price' => '', 'stock' => ''], []),
            ],
            warnings: [],
        ),
    ], []);

    app(ProductImportWriter::class)->write($company, $preview);

    $product = Product::where('company_id', $company->id)->firstOrFail();
    expect($product->variants()->count())->toBe(0);
});
