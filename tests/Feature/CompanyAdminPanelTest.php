<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the companies resource index page renders', function () {
    Company::factory()->count(2)->create();

    $this->get('/admin/companies')->assertOk();
});

test('creating a company through the resource form works end to end', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'OD Tec',
            'slug' => 'od-tec',
            'cnpj' => '53.487.318/0001-05',
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Company::where('slug', 'od-tec')->exists())->toBeTrue();
});

test('the toggle publish status action publishes and unpublishes a company', function () {
    $company = Company::factory()->create(['status' => PageStatus::Draft]);
    // A second company keeps ListCompanies from redirecting straight to
    // EditCompany, since that only happens with exactly one record.
    Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->callTableAction('togglePublishStatus', $company);

    expect($company->refresh()->status)->toBe(PageStatus::Published);

    Livewire::test(ListCompanies::class)
        ->callTableAction('togglePublishStatus', $company);

    expect($company->refresh()->status)->toBe(PageStatus::Draft);
});

test('companies can be bulk published and unpublished', function () {
    $companies = Company::factory()->count(2)->create(['status' => PageStatus::Draft]);

    Livewire::test(ListCompanies::class)
        ->callTableBulkAction('publish', $companies);

    expect($companies->fresh()->pluck('status')->all())->toBe([PageStatus::Published, PageStatus::Published]);

    Livewire::test(ListCompanies::class)
        ->callTableBulkAction('unpublish', $companies);

    expect($companies->fresh()->pluck('status')->all())->toBe([PageStatus::Draft, PageStatus::Draft]);
});

test('visiting the companies list redirects straight to edit when only one company exists', function () {
    $company = Company::factory()->create();

    $this->get('/admin/companies')->assertRedirect(EditCompany::getUrl(['record' => $company]));
});

test('the create action is hidden once a company already exists', function () {
    // Two companies, so mounting the list doesn't redirect straight to edit
    // (that only happens with exactly one record) and the table still renders.
    Company::factory()->count(2)->create();

    Livewire::test(ListCompanies::class)
        ->assertActionHidden('create');
});

test('visiting the create company page redirects to edit when a company already exists', function () {
    $company = Company::factory()->create();

    $this->get(CreateCompany::getUrl())->assertRedirect(EditCompany::getUrl(['record' => $company]));
});
