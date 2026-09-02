<?php

use App\Filament\Resources\ProductAttributes\Pages\CreateProductAttribute;
use App\Models\ProductAttribute;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

test('creating a product attribute persists the hex and image on each value', function () {
    Storage::fake('cloudinary');

    Livewire::test(CreateProductAttribute::class)
        ->fillForm([
            'name' => 'Cor',
            'values' => [
                ['value' => 'Roxo', 'hex' => '#9647B2', 'image' => [UploadedFile::fake()->image('roxo.jpg')]],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $attribute = ProductAttribute::where('name', 'Cor')->firstOrFail();
    $value = $attribute->values()->firstOrFail();

    expect($value->hex)->toBe('#9647B2');
    expect($value->image)->not->toBeNull();

    Storage::disk('cloudinary')->assertExists($value->image);
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
