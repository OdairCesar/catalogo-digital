<?php

use App\Models\Product;
use App\Models\ProductAvailability;
use App\Models\ProductInventory;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Support\Carbon;

test('a product without overrides uses its own base price and stock', function () {
    $product = Product::factory()->create(['base_price' => 100, 'base_sale_price' => 80, 'base_stock' => 5]);

    expect($product->effectivePrice())->toBe(100.0)
        ->and($product->effectiveSalePrice())->toBe(80.0)
        ->and($product->effectiveStock())->toBe(5);
});

test('a store specific inventory row overrides the product base price and stock', function () {
    $product = Product::factory()->create(['base_price' => 100, 'base_stock' => 5]);
    $store = Store::factory()->create(['company_id' => $product->company_id]);

    ProductInventory::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'price' => 150,
        'quantity' => 2,
    ]);

    expect($product->effectivePrice($store))->toBe(150.0)
        ->and($product->effectiveStock($store))->toBe(2);
});

test('a product without a matching store override falls back to its base price', function () {
    $product = Product::factory()->create(['base_price' => 100]);
    $otherStore = Store::factory()->create(['company_id' => $product->company_id]);

    expect($product->effectivePrice($otherStore))->toBe(100.0);
});

test('a variant without its own price falls back to the parent product base price', function () {
    $product = Product::factory()->create(['base_price' => 100, 'base_stock' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => null, 'stock' => null]);

    expect($variant->effectivePrice())->toBe(100.0)
        ->and($variant->effectiveStock())->toBe(10);
});

test('a variant with its own price overrides the parent product base price', function () {
    $product = Product::factory()->create(['base_price' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 120]);

    expect($variant->effectivePrice())->toBe(120.0);
});

test('a store override on a variant takes precedence over the variant own price', function () {
    $product = Product::factory()->create(['base_price' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 120]);
    $store = Store::factory()->create(['company_id' => $product->company_id]);

    ProductInventory::factory()->create([
        'store_id' => $store->id,
        'product_id' => null,
        'product_variant_id' => $variant->id,
        'price' => 90,
    ]);

    expect($variant->effectivePrice($store))->toBe(90.0);
});

test('a variant without its own image or cubage falls back to the parent product values', function () {
    $product = Product::factory()->create([
        'cover_image' => 'products/main.jpg',
        'weight_kg' => 1.5,
        'height_cm' => 10,
        'width_cm' => 20,
        'length_cm' => 30,
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'image' => null,
        'weight_kg' => null,
        'height_cm' => null,
        'width_cm' => null,
        'length_cm' => null,
    ]);

    expect($variant->effectiveImage())->toBe('products/main.jpg')
        ->and($variant->effectiveWeightKg())->toBe(1.5)
        ->and($variant->effectiveHeightCm())->toBe(10.0)
        ->and($variant->effectiveWidthCm())->toBe(20.0)
        ->and($variant->effectiveLengthCm())->toBe(30.0);
});

test('a variant with its own image and cubage overrides the parent product values', function () {
    $product = Product::factory()->create(['cover_image' => 'products/main.jpg', 'weight_kg' => 1.5]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'image' => 'variants/blue.jpg',
        'weight_kg' => 2.2,
    ]);

    expect($variant->effectiveImage())->toBe('variants/blue.jpg')
        ->and($variant->effectiveWeightKg())->toBe(2.2);
});

test('weekday is always cast to an integer regardless of how the driver returned it', function () {
    $availability = ProductAvailability::factory()->make();
    $availability->setRawAttributes(array_merge($availability->getAttributes(), ['weekday' => '3']), true);

    expect($availability->weekday)->toBe(3);
});

test('a product with no availability rules is always available', function () {
    $product = Product::factory()->create();

    expect($product->isAvailableNow())->toBeTrue();
});

test('a product is only available within its scheduled weekday and time window', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-24 12:00:00')); // a Monday

    $product = Product::factory()->create();

    ProductAvailability::factory()->create([
        'product_id' => $product->id,
        'weekday' => 1,
        'starts_at' => '11:00:00',
        'ends_at' => '14:00:00',
    ]);

    expect($product->isAvailableNow())->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-08-24 15:00:00'));
    $product->refresh();

    expect($product->isAvailableNow())->toBeFalse();

    Carbon::setTestNow();
});

test('a product is available during an overnight window that wraps past midnight', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-24 23:00:00')); // a Monday, 23:00

    $product = Product::factory()->create();

    ProductAvailability::factory()->create([
        'product_id' => $product->id,
        'weekday' => 1,
        'starts_at' => '22:00:00',
        'ends_at' => '02:00:00',
    ]);

    expect($product->isAvailableNow())->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-08-24 18:00:00'));
    $product->refresh();

    expect($product->isAvailableNow())->toBeFalse();

    Carbon::setTestNow();
});
