<?php

use App\Filament\Resources\Stores\Pages\CreateStore;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the stores resource index page renders', function () {
    Store::factory()->count(2)->create();

    $this->get('/admin/stores')->assertOk();
});

test('creating a store through the resource form works end to end', function () {
    $company = Company::factory()->create(['name' => 'OD Tec']);

    Livewire::test(CreateStore::class)
        ->fillForm([
            'company_id' => $company->id,
            'name' => 'Matriz',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $store = Store::where('name', 'Matriz')->first();

    expect($store)->not->toBeNull();
    expect($store->company_id)->toBe($company->id);
});
