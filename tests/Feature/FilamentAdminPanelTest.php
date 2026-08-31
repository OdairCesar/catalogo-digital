<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Cities\Pages\ListCities;
use App\Filament\Resources\LandingPages\Pages\ListLandingPages;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Jobs\RegenerateLandingPages;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Lead;
use App\Models\Section;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Images\CreateResponse as ImageCreateResponse;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('guests are redirected to the admin login page', function () {
    auth()->logout();

    $this->get('/admin/services')->assertRedirect('/admin/login');
});

test('the services resource index page renders', function () {
    Service::factory()->count(2)->create();

    $this->get('/admin/services')->assertOk();
});

test('the cities resource index page renders', function () {
    City::factory()->count(2)->create();

    $this->get('/admin/cities')->assertOk();
});

test('the landing pages resource index page renders', function () {
    Service::factory()->create();
    City::factory()->create();

    expect(LandingPage::count())->toBeGreaterThan(0);

    $this->get('/admin/landing-pages')->assertOk();
});

test('the leads resource index page renders and has no create route', function () {
    Lead::factory()->count(2)->create();

    $this->get('/admin/leads')->assertOk();
    $this->get('/admin/leads/create')->assertNotFound();
});

test('an editor cannot view leads', function () {
    $this->actingAs(User::factory()->create());

    Lead::factory()->count(2)->create();

    $this->get('/admin/leads')->assertForbidden();
});

test('creating a service through the resource form works end to end', function () {
    Livewire::test(CreateService::class)
        ->fillForm([
            'slug' => 'criacao-de-sites',
            'name' => 'Criação de sites',
            'title' => 'Criação de Sites',
            'subtitle' => 'Sites rápidos e profissionais',
            'description' => 'Criamos sites modernos e otimizados.',
            'benefits' => ['Rápido', 'Responsivo'],
            'faq' => [['question' => 'Quanto custa?', 'answer' => 'Depende do escopo.']],
            'keywords' => ['sites', 'landing pages'],
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::where('slug', 'criacao-de-sites')->exists())->toBeTrue();
});

test('the services resource index page renders when a service has a hero image on the cloudinary disk', function () {
    Service::factory()->create(['hero_image' => 'services/example-hero']);

    $this->get('/admin/services')->assertOk();
});

test('the generate hero image action fills the hero_image field with an ai generated image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $path = Livewire::test(CreateService::class)
        ->fillForm([
            'title' => 'Criação de Sites',
            'subtitle' => 'Sites rápidos e profissionais',
            'description' => 'Criamos sites modernos e otimizados.',
            'benefits' => ['Rápido', 'Responsivo'],
        ])
        ->callAction(TestAction::make('generateHeroImage')->schemaComponent('hero_image'))
        ->assertNotified()
        ->get('data.hero_image');

    expect($path)->not->toBeNull();

    Storage::disk('cloudinary')->assertExists($path);
});

test('the generate hero image action is disabled until a title is filled in', function () {
    Livewire::test(CreateService::class)
        ->assertActionDisabled(TestAction::make('generateHeroImage')->schemaComponent('hero_image'))
        ->fillForm(['title' => 'Criação de Sites'])
        ->assertActionEnabled(TestAction::make('generateHeroImage')->schemaComponent('hero_image'));
});

test('the services table view on site action only shows for published services', function () {
    $published = Service::factory()->create(['status' => PageStatus::Published]);
    $draft = Service::factory()->create(['status' => PageStatus::Draft]);

    Livewire::test(ListServices::class)
        ->assertTableActionVisible('viewOnSite', $published)
        ->assertTableActionHidden('viewOnSite', $draft);
});

test('the services table generate image action fills the hero_image field with an ai generated image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $service = Service::factory()->create(['title' => 'Criação de Sites', 'hero_image' => null]);

    Livewire::test(ListServices::class)
        ->callTableAction('generateImage', $service)
        ->assertNotified();

    $service->refresh();

    expect($service->hero_image)->not->toBeNull();
    Storage::disk('cloudinary')->assertExists($service->hero_image);
});

test('the generate image action is hidden once a service already has a hero image', function () {
    $service = Service::factory()->create(['hero_image' => 'services/example-hero']);

    Livewire::test(ListServices::class)
        ->assertTableActionHidden('generateImage', $service);
});

test('the portfolio items table generate image action fills the cover_image field with an ai generated image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $portfolioItem = Section::factory()->portfolio()->create(['title' => 'Plataforma de agendamento', 'image' => null]);

    Livewire::test(ListPortfolios::class)
        ->callTableAction('generateImage', $portfolioItem)
        ->assertNotified();

    $portfolioItem->refresh();

    expect($portfolioItem->image)->not->toBeNull();
    Storage::disk('cloudinary')->assertExists($portfolioItem->image);
});

test('the portfolio items generate image action is hidden once a cover image already exists', function () {
    $portfolioItem = Section::factory()->portfolio()->create(['image' => 'portfolio/example-cover']);

    Livewire::test(ListPortfolios::class)
        ->assertTableActionHidden('generateImage', $portfolioItem);
});

test('clicking a service table row always opens the edit page, not the site', function () {
    $published = Service::factory()->create(['status' => PageStatus::Published]);

    $recordUrl = Livewire::test(ListServices::class)
        ->instance()
        ->getTable()
        ->getRecordUrl($published);

    expect($recordUrl)->toBe(EditService::getUrl(['record' => $published]));
});

test('the edit service page view on site action only shows for published services', function () {
    $published = Service::factory()->create(['status' => PageStatus::Published]);
    $draft = Service::factory()->create(['status' => PageStatus::Draft]);

    Livewire::test(EditService::class, ['record' => $published->getRouteKey()])
        ->assertActionVisible('viewOnSite');

    Livewire::test(EditService::class, ['record' => $draft->getRouteKey()])
        ->assertActionHidden('viewOnSite');
});

test('the regenerate all action dispatches the regeneration job and notifies the admin', function () {
    Bus::fake();

    Livewire::test(ListLandingPages::class)
        ->callAction('regenerateAll')
        ->assertNotified();

    Bus::assertDispatched(RegenerateLandingPages::class);
    expect(Cache::has(RegenerateLandingPages::LOCK_KEY))->toBeTrue();
});

test('the regenerate all action does not dispatch another job while one is already running', function () {
    Bus::fake();
    Cache::put(RegenerateLandingPages::LOCK_KEY, true, now()->addMinutes(30));

    Livewire::test(ListLandingPages::class)
        ->callAction('regenerateAll')
        ->assertNotified();

    Bus::assertNotDispatched(RegenerateLandingPages::class);
});

test('the toggle publish status action publishes and unpublishes a service', function () {
    $service = Service::factory()->create(['status' => PageStatus::Draft]);

    Livewire::test(ListServices::class)
        ->callTableAction('togglePublishStatus', $service);

    expect($service->refresh()->status)->toBe(PageStatus::Published);

    Livewire::test(ListServices::class)
        ->callTableAction('togglePublishStatus', $service);

    expect($service->refresh()->status)->toBe(PageStatus::Draft);
});

test('services can be bulk published and unpublished', function () {
    $services = Service::factory()->count(2)->create(['status' => PageStatus::Draft]);

    Livewire::test(ListServices::class)
        ->callTableBulkAction('publish', $services);

    expect($services->fresh()->pluck('status')->all())->toBe([PageStatus::Published, PageStatus::Published]);

    Livewire::test(ListServices::class)
        ->callTableBulkAction('unpublish', $services);

    expect($services->fresh()->pluck('status')->all())->toBe([PageStatus::Draft, PageStatus::Draft]);
});

test('the cities list can be filtered by region', function () {
    $capital = City::factory()->create(['region' => 'Grande São Paulo']);
    $interior = City::factory()->create(['region' => 'Interior de São Paulo']);

    Livewire::test(ListCities::class)
        ->assertCanSeeTableRecords([$capital, $interior])
        ->filterTable('region', 'Grande São Paulo')
        ->assertCanSeeTableRecords([$capital])
        ->assertCanNotSeeTableRecords([$interior]);
});

test('leads can be bulk marked as read and unread', function () {
    $leads = Lead::factory()->count(2)->create(['read_at' => null]);

    Livewire::test(ListLeads::class)
        ->callTableBulkAction('markAsRead', $leads);

    expect($leads->fresh()->pluck('read_at')->filter()->count())->toBe(2);

    Livewire::test(ListLeads::class)
        ->callTableBulkAction('markAsUnread', $leads);

    expect($leads->fresh()->pluck('read_at')->filter()->count())->toBe(0);
});

test('the leads list can be filtered by the date received', function () {
    $old = Lead::factory()->create(['created_at' => now()->subDays(10)]);
    $recent = Lead::factory()->create(['created_at' => now()]);

    Livewire::test(ListLeads::class)
        ->assertCanSeeTableRecords([$old, $recent])
        ->filterTable('created_at', ['from' => now()->subDay()->toDateString()])
        ->assertCanSeeTableRecords([$recent])
        ->assertCanNotSeeTableRecords([$old]);
});
