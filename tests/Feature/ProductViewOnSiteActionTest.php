<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the edit product page view on site action only shows for published products', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $published = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);
    $draft = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Draft]);

    Livewire::test(EditProduct::class, ['record' => $published->getRouteKey()])
        ->assertActionVisible('viewOnSite');

    Livewire::test(EditProduct::class, ['record' => $draft->getRouteKey()])
        ->assertActionHidden('viewOnSite');
});

test('the edit product page view on site action is hidden when the product is published but its company is not', function () {
    $draftCompany = Company::factory()->create(['status' => PageStatus::Draft]);
    $product = Product::factory()->create(['company_id' => $draftCompany->id, 'status' => PageStatus::Published]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertActionHidden('viewOnSite');
});

test('the product list view on site action only shows for published products', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $published = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);
    $draft = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Draft]);

    Livewire::test(ListProducts::class)
        ->assertTableActionVisible('viewOnSite', $published)
        ->assertTableActionHidden('viewOnSite', $draft);
});

test('the product list view on site action links to the product page', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);

    Livewire::test(ListProducts::class)
        ->assertTableActionHasUrl('viewOnSite', route('products.show', $product), $product);
});

test('the edit company view feed action only shows when the company has an active product', function () {
    $withActiveProduct = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create(['company_id' => $withActiveProduct->id, 'status' => PageStatus::Published]);

    $withoutProducts = Company::factory()->create(['status' => PageStatus::Published]);

    Livewire::test(EditCompany::class, ['record' => $withActiveProduct->getRouteKey()])
        ->assertActionVisible('viewOnSite');

    Livewire::test(EditCompany::class, ['record' => $withoutProducts->getRouteKey()])
        ->assertActionHidden('viewOnSite');
});

test('the products list view feed action shows when the current company has an active product', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);

    Livewire::test(ListProducts::class)
        ->assertActionVisible('viewOnSite')
        ->assertActionHasUrl('viewOnSite', route('products.feed', $company));
});

test('the products list view feed action is hidden when the current company has no active product', function () {
    Company::factory()->create(['status' => PageStatus::Published]);

    Livewire::test(ListProducts::class)
        ->assertActionHidden('viewOnSite');
});
