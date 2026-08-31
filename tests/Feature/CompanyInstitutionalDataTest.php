<?php

use App\Models\Company;
use App\Services\Seo\StructuredDataService;
use Illuminate\Support\Facades\Storage;

test('current returns null when no company exists', function () {
    expect(Company::current())->toBeNull();
});

test('current reflects the company data after an update, thanks to the observer invalidating the cache', function () {
    $company = Company::factory()->create(['name' => 'Empresa Teste']);

    expect(Company::current()->name)->toBe('Empresa Teste');

    $company->update(['name' => 'Empresa Atualizada']);

    expect(Company::current()->name)->toBe('Empresa Atualizada');
});

test('displayName falls back to name when site_name is not set', function () {
    $company = Company::factory()->make(['name' => 'Razão Social Ltda', 'site_name' => null]);

    expect($company->displayName())->toBe('Razão Social Ltda');

    $company->site_name = 'Nome do Site';
    expect($company->displayName())->toBe('Nome do Site');
});

test('whatsappDigits normalizes a formatted number into a wa.me-ready string', function () {
    $company = Company::factory()->make(['whatsapp' => '(14) 99274-6599']);

    expect($company->whatsappDigits())->toBe('5514992746599');
});

test('whatsappDigits still prepends the country code for a DDD 55 area code number', function () {
    // A Rio Grande do Sul number: DDD 55 must not be mistaken for the +55 country code already being present.
    $company = Company::factory()->make(['whatsapp' => '(55) 3222-1100']);

    expect($company->whatsappDigits())->toBe('555532221100');
});

test('whatsappDigits leaves a number that already includes the country code untouched', function () {
    $company = Company::factory()->make(['whatsapp' => '+55 14 99274-6599']);

    expect($company->whatsappDigits())->toBe('5514992746599');
});

test('the home page header shows the company whatsapp link when a company exists', function () {
    Company::factory()->create(['whatsapp' => '(14) 99274-6599']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('5514992746599', false);
});

test('the home page og:site_name reflects the company display name', function () {
    Company::factory()->create(['name' => 'Razão Social', 'site_name' => 'Minha Empresa']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('og:site_name" content="Minha Empresa"', false);
});

test('structured data organization enriches the schema with company contact details', function () {
    Company::factory()->create([
        'site_name' => 'Minha Empresa',
        'whatsapp' => '(14) 99274-6599',
        'email' => 'contato@odairferreira.com',
    ]);

    $data = app(StructuredDataService::class)->organization();

    expect($data['name'])->toBe('Minha Empresa')
        ->and($data['telephone'])->toBe('(14) 99274-6599')
        ->and($data['email'])->toBe('contato@odairferreira.com');
});

test('formattedAddress combines the filled parts and skips the empty ones', function () {
    $company = Company::factory()->make([
        'address_street' => 'Rua das Flores',
        'address_number' => '123',
        'address_complement' => null,
        'address_neighborhood' => 'Centro',
        'address_city' => 'Bauru',
        'address_state' => 'SP',
        'address_zip_code' => '17010-000',
    ]);

    expect($company->formattedAddress())->toBe('Rua das Flores, 123 - Centro - Bauru/SP - 17010-000');
});

test('formattedAddress returns null when no address field is filled in', function () {
    $company = Company::factory()->make();

    expect($company->formattedAddress())->toBeNull();
});

test('organization exposes a structured PostalAddress once address fields are filled in', function () {
    Company::factory()->create([
        'address_street' => 'Rua das Flores',
        'address_number' => '123',
        'address_city' => 'Bauru',
        'address_state' => 'SP',
        'address_zip_code' => '17010-000',
    ]);

    $data = app(StructuredDataService::class)->organization();

    expect($data['address']['@type'])->toBe('PostalAddress')
        ->and($data['address']['streetAddress'])->toBe('Rua das Flores, 123')
        ->and($data['address']['addressLocality'])->toBe('Bauru')
        ->and($data['address']['addressRegion'])->toBe('SP')
        ->and($data['address']['postalCode'])->toBe('17010-000')
        ->and($data['address']['addressCountry'])->toBe('BR');
});

test('organization omits the address key when no address field is filled in', function () {
    Company::factory()->create();

    $data = app(StructuredDataService::class)->organization();

    expect($data)->not->toHaveKey('address');
});

test('openingHoursDisplay groups consecutive days that share the same schedule', function () {
    $hours = [];

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
        $hours[$day] = ['closed' => false, 'open' => '08:00', 'close' => '18:00'];
    }
    $hours['saturday'] = ['closed' => false, 'open' => '08:00', 'close' => '12:00'];
    $hours['sunday'] = ['closed' => true, 'open' => null, 'close' => null];

    $company = Company::factory()->make(['opening_hours' => $hours]);

    expect($company->openingHoursDisplay())->toBe('Seg a Sex: 08:00 às 18:00, Sáb: 08:00 às 12:00');
});

test('openingHoursDisplay returns null when there are no opening hours set', function () {
    $company = Company::factory()->make(['opening_hours' => null]);

    expect($company->openingHoursDisplay())->toBeNull();
});

test('logoUrl and faviconUrl return null when not set', function () {
    $company = Company::factory()->make(['logo' => null, 'favicon' => null]);

    expect($company->logoUrl())->toBeNull()
        ->and($company->faviconUrl())->toBeNull();
});

test('logoUrl and faviconUrl build a URL from the stored cloudinary path', function () {
    Storage::fake('cloudinary');

    $company = Company::factory()->make(['logo' => 'logo.png', 'favicon' => 'favicon.png']);

    expect($company->logoUrl())->toContain('logo.png')
        ->and($company->faviconUrl())->toContain('favicon.png');
});

test('the home page uses the company logo and favicon when they are set', function () {
    Storage::fake('cloudinary');

    Company::factory()->create(['logo' => 'meu-logo.png', 'favicon' => 'meu-favicon.png']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('meu-logo.png', false)
        ->assertSee('meu-favicon.png', false);
});
