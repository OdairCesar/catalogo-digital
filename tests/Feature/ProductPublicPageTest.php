<?php

use App\Enums\PageStatus;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\SectionTypeSetting;
use Illuminate\Support\Facades\DB;

test('product show page resolves for a published product with a published company', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'title' => 'Camiseta Básica',
        'base_price' => 49.9,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Camiseta Básica');
});

test('product show page 404s for a draft product', function () {
    $product = Product::factory()->create(['status' => PageStatus::Draft]);

    $this->get(route('products.show', $product))->assertNotFound();
});

test('product show page 404s when the owning company is not published', function () {
    $company = Company::factory()->create(['status' => PageStatus::Draft]);
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);

    $this->get(route('products.show', $product))->assertNotFound();
});

test('product show page falls back to a variant image when the product has no cover image', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'cover_image' => null]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'image' => 'products/variant-fallback.jpg']);

    $response = $this->get(route('products.show', $product))->assertOk();

    $response->assertSee('products/variant-fallback.jpg', false);
});

test('product show page emits Open Graph product meta tags', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'brand' => 'OD Wear',
        'base_price' => 49.9,
        'base_stock' => 5,
    ]);

    $response = $this->get(route('products.show', $product))->assertOk();

    $response->assertSee('<meta property="og:type" content="product">', false)
        ->assertSee('<meta property="product:price:amount" content="49.90">', false)
        ->assertSee('<meta property="product:price:currency" content="BRL">', false)
        ->assertSee('<meta property="product:availability" content="in stock">', false)
        ->assertSee('<meta property="product:brand" content="OD Wear">', false);
});

test('product show page marks Open Graph availability as out of stock when there is no stock left', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'base_stock' => 0]);

    $response = $this->get(route('products.show', $product))->assertOk();

    $response->assertSee('<meta property="product:availability" content="out of stock">', false);
});

test('product show page lists its active variants', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);
    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);

    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true]);
    $variant->attributeValues()->attach($blue->id);

    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => false]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Azul');
});

test('the product show page does not issue extra queries per variant', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);

    // Warm the institutional data cache so its one-off lookup (shared by every
    // page's header/footer/SEO) doesn't skew the per-request query comparison below.
    Company::current();
    SectionTypeSetting::enabledMap();

    $color = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Vermelho']);
    $green = ProductAttributeValue::factory()->create(['product_attribute_id' => $color->id, 'value' => 'Verde']);

    // Both products are created up front (rather than one per request) so each
    // request's "related products" lookup sees the same number of candidates —
    // otherwise the comparison below would be skewed by that unrelated query,
    // not by the variant count this test actually cares about.
    $productWithOneVariant = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);
    ProductVariant::factory()->create(['product_id' => $productWithOneVariant->id, 'is_active' => true])
        ->attributeValues()->attach($blue->id);

    $productWithThreeVariants = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);
    foreach ([$blue, $red, $green] as $value) {
        ProductVariant::factory()->create(['product_id' => $productWithThreeVariants->id, 'is_active' => true])
            ->attributeValues()->attach($value->id);
    }

    DB::enableQueryLog();
    $this->get(route('products.show', $productWithOneVariant))->assertOk();
    $queriesForOneVariant = count(DB::getQueryLog());
    DB::flushQueryLog();

    $this->get(route('products.show', $productWithThreeVariants))->assertOk();
    $queriesForThreeVariants = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queriesForThreeVariants)->toBe($queriesForOneVariant);
});
