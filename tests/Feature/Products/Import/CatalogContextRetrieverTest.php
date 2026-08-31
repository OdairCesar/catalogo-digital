<?php

use App\Models\Company;
use App\Models\ProductCategory;
use App\Services\Ai\Embedder;
use App\Services\Products\Import\CatalogContextRetriever;
use Illuminate\Support\Facades\Cache;

/**
 * @return Embedder&object{calls: array<int, array<int, string>>}
 */
function fakeEmbedder(): Embedder
{
    return new class implements Embedder
    {
        /** @var array<int, array<int, string>> */
        public array $calls = [];

        public function embed(array $inputs): array
        {
            $this->calls[] = array_values($inputs);

            return array_map(
                fn (string $input): array => array_fill(0, 4, (float) (strlen($input) % 5)),
                array_values($inputs),
            );
        }
    };
}

test('caches each catalog term under its own key, not one entry for the whole batch', function () {
    $company = Company::factory()->create();
    ProductCategory::factory()->create(['name' => 'Roupas']);

    $embedder = fakeEmbedder();
    app()->instance(Embedder::class, $embedder);

    app(CatalogContextRetriever::class)->retrieve($company, ['Categoria']);

    expect(Cache::has('products:import:term-embedding:'.md5('category: Roupas')))->toBeTrue();
});

test('only embeds catalog terms that are not already cached', function () {
    $company = Company::factory()->create();
    ProductCategory::factory()->create(['name' => 'Roupas']);

    $embedder = fakeEmbedder();
    app()->instance(Embedder::class, $embedder);

    $retriever = app(CatalogContextRetriever::class);

    $retriever->retrieve($company, ['Categoria']);

    // First call: one embed() for the single catalog term, one for the query.
    expect($embedder->calls)->toHaveCount(2);
    expect($embedder->calls[0])->toBe(['category: Roupas']);

    $retriever->retrieve($company, ['Categoria']);

    // Second call: the term is now cached, so only the query gets re-embedded.
    expect($embedder->calls)->toHaveCount(3);

    ProductCategory::factory()->create(['name' => 'Eletrônicos']);

    $retriever->retrieve($company, ['Categoria']);

    // Third call: only the newly added term is missing from the cache.
    expect($embedder->calls)->toHaveCount(5);
    expect($embedder->calls[3])->toBe(['category: Eletrônicos']);
});
