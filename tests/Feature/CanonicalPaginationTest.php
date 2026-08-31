<?php

use App\Enums\PageStatus;
use App\Models\Company;
use App\Models\Post;
use App\Models\Product;
use App\Models\Section;

test('the canonical tag has no query string on the first page of a paginated listing', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->create(['company_id' => $company->id, 'status' => PageStatus::Published]);

    $response = $this->get(route('products.index'))->assertOk();

    $response->assertSee('<link rel="canonical" href="'.route('products.index').'">', false);
});

test('the canonical tag is self-referential for a deeper page of the product catalog', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->count(13)->create(['company_id' => $company->id, 'status' => PageStatus::Published]);

    $response = $this->get(route('products.index', ['page' => 2]))->assertOk();

    $response->assertSee('<link rel="canonical" href="'.route('products.index').'?page=2">', false);
});

test('the canonical tag drops filter query parameters but keeps the page number', function () {
    $company = Company::factory()->create(['status' => PageStatus::Published]);
    Product::factory()->count(13)->create(['company_id' => $company->id, 'status' => PageStatus::Published, 'brand' => 'Nike']);

    $response = $this->get(route('products.index', ['marca' => 'Nike', 'page' => 2]))->assertOk();

    $response->assertSee('<link rel="canonical" href="'.route('products.index').'?page=2">', false);
});

test('the canonical tag is self-referential for a deeper page of the blog', function () {
    Post::factory()->count(13)->published()->create();

    $response = $this->get(route('blog.index', ['page' => 2]))->assertOk();

    $response->assertSee('<link rel="canonical" href="'.route('blog.index').'?page=2">', false);
});

test('the canonical tag is self-referential for a deeper page of the portfolio', function () {
    Section::factory()->count(13)->portfolio()->published()->create();

    $response = $this->get(route('portfolio.index', ['page' => 2]))->assertOk();

    $response->assertSee('<link rel="canonical" href="'.route('portfolio.index').'?page=2">', false);
});
