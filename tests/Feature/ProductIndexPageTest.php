<?php

use App\Enums\PageStatus;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\ProductVariant;

test('product index only lists active products', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $published = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Publicado']);
    Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Draft, 'title' => 'Produto Rascunho']);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee('Produto Rascunho');
});

test('product index shows the price and an out of stock badge on each product card', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'title' => 'Produto Esgotado',
        'base_price' => 123,
        'base_stock' => 0,
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('123,00')
        ->assertSee('Esgotado');
});

test('product index card falls back to a variant image when the product has no cover image', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $product = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'cover_image' => null]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'image' => 'products/variant-fallback.jpg']);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('products/variant-fallback.jpg', false);
});

test('product index page keeps the default website Open Graph type', function () {
    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('<meta property="og:type" content="website">', false);
});

test('product index filtered by category only lists products in that category', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $categoryA = ProductCategory::factory()->create();
    $categoryB = ProductCategory::factory()->create();

    $matching = Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'product_category_id' => $categoryA->id,
        'title' => 'Produto Da Categoria A',
    ]);
    Product::factory()->create([
        'company_id' => $company->id,
        'status' => PageStatus::Published,
        'product_category_id' => $categoryB->id,
        'title' => 'Produto Da Categoria B',
    ]);

    $this->get(route('products.category', $categoryA))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee('Produto Da Categoria B');
});

test('product index filtered by price range only lists products within that range', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);

    $matching = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Barato', 'base_price' => 50]);
    Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Caro', 'base_price' => 500]);

    $this->get(route('products.index', ['preco_min' => 10, 'preco_max' => 100]))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee('Produto Caro');
});

test('product index filtered by a registered attribute value only lists products with a matching variant', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    $attribute = ProductAttribute::factory()->create(['name' => 'Cor']);
    $blue = ProductAttributeValue::factory()->create(['product_attribute_id' => $attribute->id, 'value' => 'Azul']);
    $red = ProductAttributeValue::factory()->create(['product_attribute_id' => $attribute->id, 'value' => 'Vermelho']);

    $matching = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Azul']);
    $matchingVariant = ProductVariant::factory()->create(['product_id' => $matching->id, 'is_active' => true]);
    $matchingVariant->attributeValues()->attach($blue->id);

    $other = Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'title' => 'Produto Vermelho']);
    $otherVariant = ProductVariant::factory()->create(['product_id' => $other->id, 'is_active' => true]);
    $otherVariant->attributeValues()->attach($red->id);

    $this->get(route('products.index', ['atributos' => [$blue->id]]))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee('Produto Vermelho');
});
