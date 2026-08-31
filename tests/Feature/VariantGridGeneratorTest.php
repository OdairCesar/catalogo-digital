<?php

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Services\Products\VariantGridGenerator;

test('it generates the cartesian product of selected attribute values across two attributes', function () {
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Vermelho']);

    $size = ProductAttribute::factory()->create(['name' => 'Tamanho']);
    $m = ProductAttributeValue::factory()->create(['product_attribute_id' => $size->id, 'value' => 'M']);
    $g = ProductAttributeValue::factory()->create(['product_attribute_id' => $size->id, 'value' => 'G']);

    $result = app(VariantGridGenerator::class)->generate([$blue->id, $red->id, $m->id, $g->id]);

    expect($result)->toHaveCount(4);

    $skus = collect($result)->pluck('sku')->unique()->values()->all();
    expect($skus)->toBe([null]);

    $attributeValueSets = collect($result)->map(fn (array $row): array => collect($row['attributeValues'])->sort()->values()->all())->all();
    expect($attributeValueSets)->toContain(collect([$blue->id, $m->id])->sort()->values()->all());
});

test('it generates one variant per value for a single attribute', function () {
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Vermelho']);

    $result = app(VariantGridGenerator::class)->generate([$blue->id, $red->id]);

    expect($result)->toHaveCount(2);
});

test('it does not duplicate a combination that already exists among the current variants', function () {
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Vermelho']);

    $existing = [
        'existing-uuid' => [
            'sku' => 'JA-EXISTE',
            'attributeValues' => [$blue->id],
        ],
    ];

    $result = app(VariantGridGenerator::class)->generate([$blue->id, $red->id], $existing);

    expect($result)->toHaveCount(2);
    expect($result['existing-uuid']['sku'])->toBe('JA-EXISTE');
});

test('it does not duplicate a combination when the existing ids arrive as strings from the form state', function () {
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Vermelho']);

    $existing = [
        'existing-uuid' => [
            'sku' => 'JA-EXISTE',
            'attributeValues' => [(string) $blue->id],
        ],
    ];

    $result = app(VariantGridGenerator::class)->generate([$blue->id, $red->id], $existing);

    expect($result)->toHaveCount(2);
});

test('it returns the existing variants unchanged when no attribute values are selected', function () {
    $existing = ['uuid' => ['sku' => 'ALGO']];

    $result = app(VariantGridGenerator::class)->generate([], $existing);

    expect($result)->toBe($existing);
});

test('it creates the attributes and values on the fly from typed names and generates the grid', function () {
    $result = app(VariantGridGenerator::class)->generateFromAttributeGroups([
        ['name' => 'Cor', 'values' => ['Azul', 'Vermelho']],
        ['name' => 'Tamanho', 'values' => ['P', 'M']],
    ]);

    expect($result)->toHaveCount(4);
    expect(ProductAttribute::where('name', 'Cor')->exists())->toBeTrue();
    expect(ProductAttribute::where('name', 'Tamanho')->exists())->toBeTrue();

    $color = ProductAttribute::where('name', 'Cor')->firstOrFail();
    expect($color->values()->pluck('value')->sort()->values()->all())->toBe(['Azul', 'Vermelho']);
});

test('it reuses an existing attribute and value instead of creating duplicates', function () {
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);

    app(VariantGridGenerator::class)->generateFromAttributeGroups([
        ['name' => 'Cor', 'values' => ['Azul', 'Vermelho']],
    ]);

    expect(ProductAttribute::where('name', 'Cor')->count())->toBe(1);
    expect(ProductAttributeValue::where('value', 'Azul')->count())->toBe(1);
    expect(ProductAttributeValue::where('value', 'Vermelho')->count())->toBe(1);
});

test('it ignores attribute groups with a blank name or no values', function () {
    $result = app(VariantGridGenerator::class)->generateFromAttributeGroups([
        ['name' => '', 'values' => ['Azul']],
        ['name' => 'Cor', 'values' => []],
    ]);

    expect($result)->toBe([]);
});
