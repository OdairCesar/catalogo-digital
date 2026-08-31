<?php

use App\Filament\Resources\ProductAttributes\Pages\CreateProductAttribute;
use App\Models\ProductAttribute;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the product attributes resource index page renders', function () {
    ProductAttribute::factory()->create();

    $this->get('/admin/product-attributes')->assertOk();
});

test('creating a product attribute with values persists them through the repeater', function () {
    Livewire::test(CreateProductAttribute::class)
        ->fillForm([
            'name' => 'Cor',
            'values' => [
                ['value' => 'Azul'],
                ['value' => 'Vermelho'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $attribute = ProductAttribute::where('name', 'Cor')->firstOrFail();

    expect($attribute->values()->count())->toBe(2);
    expect($attribute->values()->orderBy('value')->pluck('value')->all())->toBe(['Azul', 'Vermelho']);
});

test('creating a product attribute with duplicate values fails validation', function () {
    Livewire::test(CreateProductAttribute::class)
        ->fillForm([
            'name' => 'Cor',
            'values' => [
                ['value' => 'Azul'],
                ['value' => 'azul'],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['values']);
});
