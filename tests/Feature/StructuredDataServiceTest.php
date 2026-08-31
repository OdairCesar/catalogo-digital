<?php

use App\Models\City;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Models\State;
use App\Services\Seo\StructuredDataService;

beforeEach(function () {
    $this->service = app(StructuredDataService::class);
});

test('organization returns a schema.org Organization block', function () {
    $data = $this->service->organization();

    expect($data['@type'])->toBe('Organization')
        ->and($data['name'])->toBe('Fit By Cae')
        ->and($data['url'])->toBe(route('home'));
});

test('localBusiness returns a schema.org LocalBusiness block for the city', function () {
    $city = City::factory()->create(['name' => 'Bauru', 'state_id' => State::factory()->create(['uf' => 'SP'])->id]);

    $data = $this->service->localBusiness($city);

    expect($data['@type'])->toBe('LocalBusiness')
        ->and($data['address']['addressLocality'])->toBe('Bauru')
        ->and($data['address']['addressRegion'])->toBe('SP');
});

test('service returns a schema.org Service block tied to the landing page', function () {
    $service = Service::factory()->create(['title' => 'Criação de Sites']);
    $city = City::factory()->create(['name' => 'Bauru']);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $data = $this->service->service($landingPage);

    expect($data['@type'])->toBe('Service')
        ->and($data['name'])->toBe('Criação de Sites em Bauru')
        ->and($data['areaServed']['name'])->toBe('Bauru');
});

test('service composes {cidade} tokens in the subtitle instead of leaking them into the JSON-LD', function () {
    $service = Service::factory()->create([
        'title' => 'Criação de Sites',
        'subtitle' => 'Presença digital para empresas de {cidade}.',
    ]);
    $city = City::factory()->create(['name' => 'Bauru']);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $data = $this->service->service($landingPage);

    expect($data['description'])->toBe('Presença digital para empresas de Bauru.');
});

test('serviceForClusterCity composes the cluster title/subtitle without duplicating the city name', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'title' => 'Loja Virtual para Empresas de {cidade}',
        'subtitle' => 'E-commerce rápido para empresas de {cidade}.',
    ]);
    $city = City::factory()->create(['name' => 'Bauru']);
    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();

    $data = $this->service->serviceForClusterCity($pivot);

    expect($data['name'])->toBe('Loja Virtual para Empresas de Bauru')
        ->and($data['description'])->toBe('E-commerce rápido para empresas de Bauru.');
});

test('serviceGeneric returns a schema.org Service block without a city context', function () {
    $service = Service::factory()->create(['title' => 'Criação de Sites']);

    $data = $this->service->serviceGeneric($service, 'Presença digital para sua empresa.');

    expect($data['@type'])->toBe('Service')
        ->and($data['name'])->toBe('Criação de Sites')
        ->and($data['description'])->toBe('Presença digital para sua empresa.')
        ->and($data)->not->toHaveKey('areaServed');
});

test('faqPage returns null for an empty faq list', function () {
    expect($this->service->faqPage([]))->toBeNull();
});

test('faqPage maps question/answer pairs into schema.org Question entities', function () {
    $data = $this->service->faqPage([
        ['question' => 'Vocês atendem minha cidade?', 'answer' => 'Sim, atendemos.'],
    ]);

    expect($data['@type'])->toBe('FAQPage')
        ->and($data['mainEntity'][0]['@type'])->toBe('Question')
        ->and($data['mainEntity'][0]['name'])->toBe('Vocês atendem minha cidade?')
        ->and($data['mainEntity'][0]['acceptedAnswer']['text'])->toBe('Sim, atendemos.');
});

test('blogPosting returns a schema.org BlogPosting block for the post', function () {
    $post = Post::factory()->published()->create([
        'title' => 'Como escolher um sistema para clínicas',
        'excerpt' => 'Um guia completo.',
    ]);

    $data = $this->service->blogPosting($post);

    expect($data['@type'])->toBe('BlogPosting')
        ->and($data['headline'])->toBe('Como escolher um sistema para clínicas')
        ->and($data['description'])->toBe('Um guia completo.')
        ->and($data['datePublished'])->toBe($post->published_at->toAtomString())
        ->and($data['author']['@type'])->toBe('Organization')
        ->and($data['mainEntityOfPage']['@id'])->toBe(route('blog.show', $post))
        ->and($data)->not->toHaveKey('image');
});

test('product returns a schema.org Product block with an Offer for an in-stock product', function () {
    $product = Product::factory()->create([
        'title' => 'Camiseta Básica',
        'brand' => 'OD Wear',
        'base_price' => 49.9,
        'base_stock' => 5,
    ]);

    $data = $this->service->product($product);

    expect($data['@type'])->toBe('Product')
        ->and($data['name'])->toBe('Camiseta Básica')
        ->and($data['brand']['name'])->toBe('OD Wear')
        ->and($data['offers']['@type'])->toBe('Offer')
        ->and($data['offers']['price'])->toBe(49.9)
        ->and($data['offers']['priceCurrency'])->toBe('BRL')
        ->and($data['offers']['availability'])->toBe('https://schema.org/InStock')
        ->and($data['offers']['url'])->toBe(route('products.show', $product));
});

test('product marks availability as OutOfStock when there is no stock left', function () {
    $product = Product::factory()->create(['base_stock' => 0]);

    $data = $this->service->product($product);

    expect($data['offers']['availability'])->toBe('https://schema.org/OutOfStock');
});

test('product falls back to the first active variant image when there is no cover image', function () {
    $product = Product::factory()->create(['cover_image' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => false, 'image' => 'products/inactive.jpg']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'image' => 'products/variant.jpg']);

    $data = $this->service->product($product);

    expect($data['image'])->toContain('products/variant.jpg');
});

test('product omits the image key when there is no cover image and no variant image', function () {
    $product = Product::factory()->create(['cover_image' => null]);

    $data = $this->service->product($product);

    expect($data)->not->toHaveKey('image');
});

test('breadcrumbList numbers items by position and omits url on the last item', function () {
    $data = $this->service->breadcrumbList([
        ['label' => 'Início', 'url' => '/'],
        ['label' => 'Serviços', 'url' => '/servicos'],
        ['label' => 'Criação de Sites'],
    ]);

    expect($data['itemListElement'])->toHaveCount(3)
        ->and($data['itemListElement'][0]['position'])->toBe(1)
        ->and($data['itemListElement'][2]['position'])->toBe(3)
        ->and($data['itemListElement'][2])->not->toHaveKey('item');
});
