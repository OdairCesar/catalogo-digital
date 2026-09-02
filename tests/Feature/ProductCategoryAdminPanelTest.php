<?php

use App\Filament\Resources\ProductCategories\Pages\CreateProductCategory;
use App\Filament\Resources\ProductCategories\Pages\ListProductCategories;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the product categories resource index page renders', function () {
    ProductCategory::factory()->count(2)->create();

    $this->get('/admin/product-categories')->assertOk();
});

test('creating a product category through the resource form works end to end', function () {
    Livewire::test(CreateProductCategory::class)
        ->fillForm([
            'name' => 'Camisetas',
            'slug' => 'camisetas',
            'google_product_category' => '1604',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $category = ProductCategory::where('slug', 'camisetas')->firstOrFail();

    expect($category->google_product_category)->toBe('1604');
});

test('creating a product category persists the uploaded image', function () {
    Storage::fake('cloudinary');

    Livewire::test(CreateProductCategory::class)
        ->fillForm([
            'name' => 'Leggings',
            'slug' => 'leggings',
            'image' => [UploadedFile::fake()->image('leggings.jpg')],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $category = ProductCategory::where('slug', 'leggings')->firstOrFail();

    expect($category->image)->not->toBeNull();
    expect($category->imageUrl())->not->toBeNull();

    Storage::disk('cloudinary')->assertExists($category->image);
});

test('the product categories table lists how many products use each category', function () {
    $category = ProductCategory::factory()->create();
    Product::factory()->count(2)->create(['product_category_id' => $category->id]);

    Livewire::test(ListProductCategories::class)
        ->assertCanSeeTableRecords([$category]);
});
