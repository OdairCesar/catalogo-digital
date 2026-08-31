<?php

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Services\Products\Import\ColumnMapping;
use App\Services\Products\Import\ParsedRow;
use App\Services\Products\Import\ParsedSpreadsheet;
use App\Services\Products\Import\ProductImportMapping;
use App\Services\Products\Import\ProductImportPreviewBuilder;

test('groups rows sharing the grouping column into a single product with multiple variants', function () {
    $company = Company::factory()->create();

    $spreadsheet = new ParsedSpreadsheet(
        ['SKU pai', 'Nome', 'Cor', 'SKU variação', 'Preço'],
        [
            new ParsedRow(1, ['SKU pai' => 'CAM', 'Nome' => 'Camiseta', 'Cor' => 'Azul', 'SKU variação' => 'CAM-AZUL', 'Preço' => '39.90']),
            new ParsedRow(2, ['SKU pai' => 'CAM', 'Nome' => 'Camiseta', 'Cor' => 'Preto', 'SKU variação' => 'CAM-PRETO', 'Preço' => '39.90']),
        ],
    );

    $mapping = new ProductImportMapping('SKU pai', [
        new ColumnMapping('SKU pai', 'ignore', ''),
        new ColumnMapping('Nome', 'product_field', 'title'),
        new ColumnMapping('Cor', 'attribute', 'Cor'),
        new ColumnMapping('SKU variação', 'variant_field', 'sku'),
        new ColumnMapping('Preço', 'variant_field', 'price'),
    ]);

    $preview = app(ProductImportPreviewBuilder::class)->build($company, $spreadsheet, $mapping);

    expect($preview->products)->toHaveCount(1);

    $product = $preview->products[0];
    expect($product->fields['title'])->toBe('Camiseta');
    expect($product->variants)->toHaveCount(2);
    expect($product->variants[0]->fields['sku'])->toBe('CAM-AZUL');
    expect($product->variants[0]->attributeValues[0]->attributeName)->toBe('Cor');
    expect($product->variants[0]->attributeValues[0]->value)->toBe('Azul');
});

test('resolves an existing category and attribute value by case-insensitive exact match', function () {
    $company = Company::factory()->create();
    $category = ProductCategory::factory()->create(['name' => 'Roupas']);
    $attribute = ProductAttribute::factory()->create(['name' => 'Cor']);
    $value = ProductAttributeValue::factory()->create(['product_attribute_id' => $attribute->id, 'value' => 'Azul']);

    $spreadsheet = new ParsedSpreadsheet(
        ['Nome', 'Categoria', 'Cor'],
        [new ParsedRow(1, ['Nome' => 'Camiseta', 'Categoria' => 'roupas', 'Cor' => 'azul'])],
    );

    $mapping = new ProductImportMapping(null, [
        new ColumnMapping('Nome', 'product_field', 'title'),
        new ColumnMapping('Categoria', 'product_field', 'category'),
        new ColumnMapping('Cor', 'attribute', 'Cor'),
    ]);

    $preview = app(ProductImportPreviewBuilder::class)->build($company, $spreadsheet, $mapping);
    $product = $preview->products[0];

    expect($product->category->existingCategoryId)->toBe($category->id);
    expect($product->variants[0]->attributeValues[0]->existingAttributeValueId)->toBe($value->id);
});

test('flags an unknown category or attribute value for creation instead of reuse', function () {
    $company = Company::factory()->create();

    $spreadsheet = new ParsedSpreadsheet(
        ['Nome', 'Categoria'],
        [new ParsedRow(1, ['Nome' => 'Camiseta', 'Categoria' => 'Categoria nova'])],
    );

    $mapping = new ProductImportMapping(null, [
        new ColumnMapping('Nome', 'product_field', 'title'),
        new ColumnMapping('Categoria', 'product_field', 'category'),
    ]);

    $preview = app(ProductImportPreviewBuilder::class)->build($company, $spreadsheet, $mapping);

    expect($preview->products[0]->category->existingCategoryId)->toBeNull();
    expect($preview->products[0]->category->name)->toBe('Categoria nova');
});

test('matches an existing product in the same company by sku to mark it as an update', function () {
    $company = Company::factory()->create();
    $existing = Product::factory()->create(['company_id' => $company->id, 'sku' => 'CAM-1']);

    $spreadsheet = new ParsedSpreadsheet(
        ['Nome', 'SKU'],
        [new ParsedRow(1, ['Nome' => 'Camiseta atualizada', 'SKU' => 'cam-1'])],
    );

    $mapping = new ProductImportMapping(null, [
        new ColumnMapping('Nome', 'product_field', 'title'),
        new ColumnMapping('SKU', 'product_field', 'sku'),
    ]);

    $preview = app(ProductImportPreviewBuilder::class)->build($company, $spreadsheet, $mapping);

    expect($preview->products[0]->existingProductId)->toBe($existing->id);
});

test('warns and still includes a product row without a title', function () {
    $company = Company::factory()->create();

    $spreadsheet = new ParsedSpreadsheet(
        ['Nome'],
        [new ParsedRow(1, ['Nome' => ''])],
    );

    $mapping = new ProductImportMapping(null, [
        new ColumnMapping('Nome', 'product_field', 'title'),
    ]);

    $preview = app(ProductImportPreviewBuilder::class)->build($company, $spreadsheet, $mapping);

    expect($preview->products[0]->warnings)->not->toBeEmpty();
});
