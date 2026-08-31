<?php

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the product slug is generated automatically from the title', function () {
    Livewire::test(CreateProduct::class)
        ->set('data.title', 'Camiseta Básica de Algodão')
        ->assertSet('data.slug', 'camiseta-basica-de-algodao');
});

test('the company slug is generated automatically from the name', function () {
    Livewire::test(CreateCompany::class)
        ->set('data.name', 'OD Tec Soluções')
        ->assertSet('data.slug', 'od-tec-solucoes');
});

test('a manually edited product slug is not overwritten when the title changes again', function () {
    Livewire::test(CreateProduct::class)
        ->set('data.title', 'Camiseta Básica')
        ->assertSet('data.slug', 'camiseta-basica')
        ->set('data.slug', 'meu-slug-customizado')
        ->set('data.title', 'Camiseta Premium')
        ->assertSet('data.slug', 'meu-slug-customizado');
});
