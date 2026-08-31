<?php

use App\Enums\PageStatus;
use App\Enums\ProductAgeGroup;
use App\Enums\ProductGender;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // The feed is now cached (see finding #6); flush between tests so cached
    // content from one test's company (ids are reused after each test's
    // transaction rolls back) can't leak into the next test's assertions.
    Cache::flush();
});

test('the feed only includes active products belonging to that company', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $otherCompany = Company::factory()->create(['status' => PageStatus::Published]);

    $product = Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'title' => 'Produto Da Empresa',
    ]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $otherCompany->id,
        'status' => PageStatus::Published,
        'title' => 'Produto De Outra Empresa',
    ]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Draft,
        'title' => 'Produto Rascunho',
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertSee($product->title)
        ->assertDontSee('Produto De Outra Empresa')
        ->assertDontSee('Produto Rascunho');
});

test('the feed response is cached per company', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
    ]);

    $this->get(route('products.feed', $company))->assertOk();

    expect(Cache::has("google-shopping-feed-{$company->id}"))->toBeTrue();
});

test('the feed 404s for a company that is not published', function () {
    $company = Company::factory()->create(['status' => PageStatus::Draft]);

    $this->get(route('products.feed', $company))->assertNotFound();
});

test('the feed reads the google product category from the linked category', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $category = ProductCategory::factory()->create(['google_product_category' => '1604']);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'product_category_id' => $category->id,
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertSee('<g:google_product_category>1604</g:google_product_category>', false);
});

test('a product without variants produces a single feed item', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'base_price' => 49.9,
        'base_stock' => 3,
    ]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(substr_count($response->getContent(), '<item>'))->toBe(1);
    $response->assertSee("<g:id>product-{$product->id}</g:id>", false);
});

test('a product with active variants produces one feed item per variant sharing an item_group_id', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg', 'company_id' => $company->id, 'status' => PageStatus::Published]);

    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Vermelho']);

    $activeOne = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'price' => 39.9, 'stock' => 2]);
    $activeOne->attributeValues()->attach($blue->id);

    $activeTwo = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'price' => 39.9, 'stock' => 0]);
    $activeTwo->attributeValues()->attach($red->id);

    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => false]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(substr_count($response->getContent(), '<item>'))->toBe(2);
    $response->assertSee("<g:item_group_id>product-{$product->id}</g:item_group_id>", false);
    $response->assertSee("<g:item_group_title>{$product->title}</g:item_group_title>", false);
    $response->assertSee("<g:id>variant-{$activeOne->id}</g:id>", false);
    $response->assertSee("<g:id>variant-{$activeTwo->id}</g:id>", false);
    $response->assertSee('<g:availability>out_of_stock</g:availability>', false);
    $response->assertSee('<g:availability>in_stock</g:availability>', false);
});

test('the feed sends the sale price when one is set', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'base_price' => 49.9,
        'base_sale_price' => 39.9,
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertSee('<g:sale_price>39.90 BRL</g:sale_price>', false);
});

test('the feed marks identifier_exists as no when the product has no gtin, mpn or brand', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'cover_image' => 'products/capa.jpg',
        'gtin' => null,
        'mpn' => null,
        'brand' => null,
    ]);

    // A product without a brand is excluded from the feed entirely (see the
    // dedicated exclusion tests below), so identifier_exists is only ever
    // observable for products that DO have a brand but lack gtin AND mpn.
    Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'cover_image' => 'products/capa.jpg',
        'brand' => 'OD Wear',
        'gtin' => null,
        'mpn' => null,
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertSee('<g:identifier_exists>no</g:identifier_exists>', false);
});

test('the feed omits identifier_exists when the product has both a brand and an mpn', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'gtin' => null,
        'mpn' => 'MPN-123',
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertDontSee('identifier_exists');
});

test('the feed omits identifier_exists when the product has a gtin', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'gtin' => '7891234567895',
        'mpn' => null,
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertDontSee('identifier_exists');
});

test('the feed truncates title and description to the lengths Google accepts', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'title' => str_repeat('a', 200),
        'description' => str_repeat('b', 6000),
    ]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(mb_strlen(str_repeat('a', 200)))->toBeGreaterThan(150);
    $response->assertSee('<title>'.str_repeat('a', 150).'</title>', false);
    $response->assertSee('<description>'.str_repeat('b', 5000).'</description>', false);
});

test('the feed maps attribute names to color, size, material and pattern', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg', 'company_id' => $company->id, 'status' => PageStatus::Published]);

    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $size = ProductAttribute::factory()->create(['name' => 'Tamanho']);
    $flavor = ProductAttribute::factory()->create(['name' => 'Sabor']);

    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $medium = ProductAttributeValue::factory()->create(['product_attribute_id' => $size->id, 'value' => 'M']);
    $chocolate = ProductAttributeValue::factory()->create(['product_attribute_id' => $flavor->id, 'value' => 'Chocolate']);

    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true]);
    $variant->attributeValues()->attach([$blue->id, $medium->id, $chocolate->id]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    $response->assertSee('<g:color>Azul</g:color>', false);
    $response->assertSee('<g:size>M</g:size>', false);
    $response->assertDontSee('<g:material>', false);
    $response->assertDontSee('<g:pattern>', false);
    $response->assertSee('Chocolate'); // unmapped attribute still differentiates the variant title
});

test('the feed sends gender and age_group when set', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'gender' => ProductGender::Female,
        'age_group' => ProductAgeGroup::Adult,
    ]);

    $this->get(route('products.feed', $company))
        ->assertOk()
        ->assertSee('<g:gender>female</g:gender>', false)
        ->assertSee('<g:age_group>adult</g:age_group>', false);
});

test('a product without a brand is excluded from the feed entirely', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'cover_image' => 'products/capa.jpg',
        'brand' => null,
        'title' => 'Produto Sem Marca',
    ]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(substr_count($response->getContent(), '<item>'))->toBe(0);
});

test('a product without a cover image and without variants is excluded from the feed', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'brand' => 'OD Wear',
        'cover_image' => null,
    ]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(substr_count($response->getContent(), '<item>'))->toBe(0);
});

test('a variant without its own image and without a product cover image is skipped, but its sibling with an image is kept', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'brand' => 'OD Wear',
        'cover_image' => null,
    ]);

    $withoutImage = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'image' => null]);
    $withImage = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'image' => 'products/variant.jpg']);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(substr_count($response->getContent(), '<item>'))->toBe(1);
    $response->assertDontSee("<g:id>variant-{$withoutImage->id}</g:id>", false);
    $response->assertSee("<g:id>variant-{$withImage->id}</g:id>", false);
});

test('the feed includes additional_image_link for each gallery image', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'brand' => 'OD Wear', 'cover_image' => 'products/capa.jpg',
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'images' => ['products/extra-1.jpg', 'products/extra-2.jpg'],
    ]);

    $response = $this->get(route('products.feed', $company))->assertOk();

    expect(substr_count($response->getContent(), '<g:additional_image_link>'))->toBe(2);
});
