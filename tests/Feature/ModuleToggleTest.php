<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\LandingPages\LandingPageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\ProductAttributes\ProductAttributeResource;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Filament\Resources\ProductInventories\ProductInventoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\ServiceClusterLandingPages\ServiceClusterLandingPageResource;
use App\Filament\Resources\ServiceClusters\ServiceClusterResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\States\StateResource;
use App\Filament\Resources\Stores\StoreResource;
use App\Filament\Resources\ToolSubmissions\ToolSubmissionResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\Section;
use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('disabling the blog module 404s its public routes but leaves other routes untouched', function () {
    config(['modules.blog' => false]);

    $post = Post::factory()->published()->create();
    $category = Category::factory()->create();

    $this->get(route('blog.index'))->assertNotFound()->assertSee('redirecionado para a página inicial');
    $this->get(route('blog.category', $category))->assertNotFound();
    $this->get(route('blog.show', $post))->assertNotFound();

    $this->get(route('home'))->assertOk();
});

test('disabling the servicos module 404s its public routes but leaves other routes untouched', function () {
    $service = Service::factory()->create();
    $city = City::factory()->create();
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    config(['modules.servicos' => false]);

    $this->get(route('services.index'))->assertNotFound();
    $this->get(route('services.show', $service))->assertNotFound();
    $this->get(route('cities.index'))->assertNotFound();
    $this->get(route('cities.show', $city))->assertNotFound();
    $this->get(route('states.index'))->assertNotFound();
    $this->get(route('states.show', $city->state))->assertNotFound();
    $this->get(route('landing.show', $landingPage))->assertNotFound();

    $this->get(route('home'))->assertOk();
});

test('disabling the produtos module 404s its public routes but leaves other routes untouched', function () {
    config(['modules.produtos' => false]);

    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);

    $this->get(route('products.index'))->assertNotFound();
    $this->get(route('products.show', $product))->assertNotFound();
    $this->get(route('products.feed', $company))->assertNotFound();

    $this->get(route('home'))->assertOk();
});

test('disabling the ferramentas module 404s its public routes but leaves other routes untouched', function () {
    config(['modules.ferramentas' => false]);

    $this->get(route('tools.index'))->assertNotFound();
    $this->get(route('tools.show', 'consultor-ia'))->assertNotFound();
    $this->get('/consultor-ia')->assertNotFound();

    $this->get(route('home'))->assertOk();
});

test('all module routes work normally when every module is enabled', function () {
    $this->get(route('blog.index'))->assertOk();
    $this->get(route('services.index'))->assertOk();
    $this->get(route('cities.index'))->assertOk();
    $this->get(route('states.index'))->assertOk();
    $this->get(route('products.index'))->assertOk();
    $this->get(route('portfolio.index'))->assertOk();
    $this->get(route('tools.index'))->assertOk();
});

test('disabling a module hides its filament resources from navigation and admin access', function () {
    config(['modules.blog' => false]);

    expect(PostResource::shouldRegisterNavigation())->toBeFalse()
        ->and(PostResource::canAccess())->toBeFalse()
        ->and(CategoryResource::shouldRegisterNavigation())->toBeFalse()
        ->and(CategoryResource::canAccess())->toBeFalse();

    $this->get('/admin/posts')->assertForbidden();
    $this->get('/admin/categories')->assertForbidden();
});

test('disabling servicos hides service, location and landing page resources', function () {
    config(['modules.servicos' => false]);

    expect(ServiceResource::canAccess())->toBeFalse()
        ->and(ServiceClusterResource::canAccess())->toBeFalse()
        ->and(ServiceClusterLandingPageResource::canAccess())->toBeFalse()
        ->and(CityResource::canAccess())->toBeFalse()
        ->and(StateResource::canAccess())->toBeFalse()
        ->and(LandingPageResource::canAccess())->toBeFalse();

    $this->get('/admin/services')->assertForbidden();
    $this->get('/admin/cities')->assertForbidden();
    $this->get('/admin/states')->assertForbidden();
    $this->get('/admin/landing-pages')->assertForbidden();
});

test('disabling produtos hides the whole product cluster of resources', function () {
    config(['modules.produtos' => false]);

    expect(ProductResource::canAccess())->toBeFalse()
        ->and(ProductCategoryResource::canAccess())->toBeFalse()
        ->and(ProductAttributeResource::canAccess())->toBeFalse()
        ->and(ProductInventoryResource::canAccess())->toBeFalse()
        ->and(StoreResource::canAccess())->toBeFalse();

    $this->get('/admin/products')->assertForbidden();
    $this->get('/admin/stores')->assertForbidden();
    $this->get('/admin/product-categories')->assertForbidden();
    $this->get('/admin/product-attributes')->assertForbidden();
    $this->get('/admin/product-inventories')->assertForbidden();
});

test('the company resource stays accessible when produtos is disabled', function () {
    config(['modules.produtos' => false]);

    expect(CompanyResource::canAccess())->toBeTrue();

    $this->get('/admin/companies')->assertOk();
});

test('disabling ferramentas hides the tool submission resource', function () {
    config(['modules.ferramentas' => false]);

    expect(ToolSubmissionResource::canAccess())->toBeFalse();

    $this->get('/admin/tool-submissions')->assertForbidden();
});

test('enabled modules keep normal admin authorization behaviour', function () {
    expect(PostResource::canAccess())->toBeTrue();

    $this->get('/admin/posts')->assertOk();
});

test('sitemap.xml excludes a disabled module entirely', function () {
    config(['modules.blog' => false, 'modules.produtos' => false]);

    $post = Post::factory()->published()->create();

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertDontSee(route('blog.index'), false)
        ->assertDontSee(route('blog.show', $post), false)
        ->assertDontSee(route('products.index'), false)
        ->assertSee(route('services.index'), false);
});

test('sitemap.xml excludes cities, states and landing pages when servicos is disabled', function () {
    $city = City::factory()->create();

    config(['modules.servicos' => false]);

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertDontSee(route('services.index'), false)
        ->assertDontSee(route('cities.index'), false)
        ->assertDontSee(route('cities.show', $city), false)
        ->assertDontSee(route('states.index'), false)
        ->assertDontSee(route('states.show', $city->state), false);
});

test('sitemap.xml excludes ferramentas urls when disabled but keeps portfolio', function () {
    $item = Section::factory()->portfolio()->published()->create();

    config(['modules.ferramentas' => false]);

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee(route('portfolio.index'), false)
        ->assertSee(route('portfolio.show', $item->slug), false)
        ->assertDontSee(route('tools.index'), false)
        ->assertDontSee(route('tools.show', 'consultor-ia'), false);
});

test('the home page hides service related sections when servicos is disabled', function () {
    Service::factory()->create();
    City::factory()->create();

    config(['modules.servicos' => false]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertDontSee('Ver serviços')
        ->assertDontSee('Soluções completas para levar sua empresa ao digital')
        ->assertDontSee('Tipos de projeto que já entregamos')
        ->assertDontSee('Cidades atendidas');
});

test('the header and footer nav hide links for disabled modules', function () {
    config(['modules.blog' => false, 'modules.servicos' => false, 'modules.produtos' => false]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertDontSee('href="'.route('blog.index').'"', false)
        ->assertDontSee('href="'.route('services.index').'"', false)
        ->assertDontSee('href="'.route('cities.index').'"', false)
        ->assertDontSee('href="'.route('products.index').'"', false);
});

test('the header and footer nav show the produtos link when the module is enabled', function () {
    $response = $this->get(route('home'))->assertOk();

    $response->assertSee('href="'.route('products.index').'"', false);
});

test('the home page shows a featured products section when produtos is enabled and products exist', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Em Destaque']);

    $response = $this->get(route('home'))->assertOk();

    $response->assertSee('Produto Em Destaque');
});

test('the home page hides the featured products section when produtos is disabled', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Em Destaque']);

    config(['modules.produtos' => false]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertDontSee('Produto Em Destaque');
});

test('the home page hides the tools section when ferramentas is disabled', function () {
    config(['modules.ferramentas' => false]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertDontSee('Tem uma pergunta sobre o seu projeto? A IA responde agora');
});
