<?php

use App\Enums\PageStatus;
use App\Enums\ServiceClusterStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Section;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Models\State;

test('sitemap.xml lists static pages and published services, cities and landing pages', function () {
    $service = Service::factory()->create(['status' => PageStatus::Published]);
    $city = City::factory()->create(['status' => PageStatus::Published]);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $response = $this->get(route('sitemap'))->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');

    $response->assertSee(route('home'), false)
        ->assertSee(route('services.index'), false)
        ->assertSee(route('cities.index'), false)
        ->assertSee(route('states.index'), false)
        ->assertSee(route('faq.index'), false)
        ->assertSee(route('portfolio.index'), false)
        ->assertSee(route('services.show', $service), false)
        ->assertSee(route('cities.show', $city), false)
        ->assertSee(route('landing.show', $landingPage), false);
});

test('sitemap.xml lists published states but excludes drafts', function () {
    $state = State::factory()->create(['status' => PageStatus::Published]);
    $draftState = State::factory()->create(['status' => PageStatus::Draft]);

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee(route('states.show', $state), false)
        ->assertDontSee(route('states.show', $draftState), false);
});

test('sitemap.xml excludes draft services, cities and landing pages', function () {
    $draftService = Service::factory()->create(['status' => PageStatus::Draft]);
    $draftCity = City::factory()->create(['status' => PageStatus::Draft]);

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertDontSee($draftService->slug, false)
        ->assertDontSee($draftCity->slug, false);
});

test('sitemap.xml lists published posts and their categories, but excludes drafts', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->published()->create(['category_id' => $category->id]);
    $draftPost = Post::factory()->draft()->create();

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee(route('blog.index'), false)
        ->assertSee(route('blog.show', $post), false)
        ->assertSee(route('blog.category', $category), false)
        ->assertDontSee(route('blog.show', $draftPost), false);
});

test('sitemap.xml reflects new published records after the cache is invalidated by the observers', function () {
    $this->get(route('sitemap'))->assertOk();

    $city = City::factory()->create(['name' => 'Nova Cidade', 'status' => PageStatus::Published]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('cities.show', $city), false);
});

test('sitemap.xml reflects a newly published post after the cache is invalidated by the observer', function () {
    $this->get(route('sitemap'))->assertOk();

    $post = Post::factory()->published()->create();

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('blog.show', $post), false);
});

test('sitemap.xml lists published portfolio items but excludes drafts', function () {
    $item = Section::factory()->portfolio()->published()->create();
    $draftItem = Section::factory()->portfolio()->draft()->create();

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee(route('portfolio.show', $item->slug), false)
        ->assertDontSee(route('portfolio.show', $draftItem->slug), false);
});

test('sitemap.xml lists published service clusters and cluster landing pages, but excludes drafts', function () {
    $service = Service::factory()->create(['status' => PageStatus::Published]);
    $cluster = ServiceCluster::factory()->create(['service_id' => $service->id, 'status' => ServiceClusterStatus::Published]);
    $draftCluster = ServiceCluster::factory()->create(['service_id' => $service->id, 'status' => ServiceClusterStatus::Draft]);
    $city = City::factory()->create(['status' => PageStatus::Published]);
    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee(route('services.clusters.show', [$service, $cluster]), false)
        ->assertSee(route('services.clusters.show', [$service, $pivot->slug]), false)
        ->assertDontSee(route('services.clusters.show', [$service, $draftCluster]), false);
});

test('sitemap.xml lists published products and their categories, but excludes drafts', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'product_category_id' => $category->id]);
    $draftProduct = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Draft]);

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertSee(route('products.index'), false)
        ->assertSee(route('products.show', $product), false)
        ->assertSee(route('products.category', $category), false)
        ->assertDontSee(route('products.show', $draftProduct), false);
});

test('robots.txt disallows the admin panel and points to the sitemap', function () {
    $response = $this->get(route('robots'))->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $response->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.route('sitemap'));
});
