<?php

namespace App\Services\Products\Import;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Services\Ai\Embedder;
use Illuminate\Support\Facades\Cache;

/**
 * The RAG piece of the import: before asking the AI to map spreadsheet
 * columns/values to catalog fields, this looks up which existing categories,
 * attributes/values and brands are semantically closest to what's in the
 * spreadsheet, so the AI reuses them instead of creating near-duplicates
 * (e.g. "Coloração" when "Cor" already exists).
 *
 * Catalog term embeddings are computed on demand and cached by content hash
 * (no persistent vector index) — proportionate to catalogs that are small
 * per company and imports that happen sporadically.
 */
final class CatalogContextRetriever
{
    private const int CACHE_TTL_SECONDS = 6 * 60 * 60;

    public function __construct(private readonly Embedder $embedder) {}

    /**
     * @param  array<int, string>  $queries  spreadsheet column headers and/or sample cell values
     * @return array<string, array<int, CatalogTerm>> candidate terms per query, closest first
     */
    public function retrieve(Company $company, array $queries, int $perQuery = 5): array
    {
        $queries = array_values(array_unique(array_filter(
            array_map('trim', $queries),
            fn (string $query): bool => $query !== '',
        )));

        if ($queries === []) {
            return [];
        }

        $terms = $this->catalogTerms($company);

        if ($terms === []) {
            return array_fill_keys($queries, []);
        }

        $termEmbeddings = $this->embedTerms($terms);
        $queryEmbeddings = $this->embedder->embed($queries);

        $results = [];

        foreach ($queries as $index => $query) {
            $queryVector = $queryEmbeddings[$index] ?? null;

            if ($queryVector === null) {
                $results[$query] = [];

                continue;
            }

            $scored = [];

            foreach ($terms as $termIndex => $term) {
                $scored[] = [
                    'term' => $term,
                    'score' => $this->cosineSimilarity($queryVector, $termEmbeddings[$termIndex]),
                ];
            }

            usort($scored, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

            $results[$query] = array_map(
                fn (array $row): CatalogTerm => $row['term'],
                array_slice($scored, 0, $perQuery),
            );
        }

        return $results;
    }

    /**
     * @return array<int, CatalogTerm>
     */
    private function catalogTerms(Company $company): array
    {
        $terms = [];

        foreach (ProductCategory::query()->get(['id', 'name']) as $category) {
            $terms[] = new CatalogTerm('category', $category->name, $category->id);
        }

        foreach (ProductAttribute::query()->get(['id', 'name']) as $attribute) {
            $terms[] = new CatalogTerm('attribute', $attribute->name, $attribute->id);
        }

        foreach (ProductAttributeValue::query()->with('attribute')->get() as $value) {
            $terms[] = new CatalogTerm('attribute_value', $value->label(), $value->id);
        }

        $brands = Product::query()
            ->where('company_id', $company->id)
            ->whereNotNull('brand')
            ->distinct()
            ->pluck('brand');

        foreach ($brands as $brand) {
            if (is_string($brand)) {
                $terms[] = new CatalogTerm('brand', $brand);
            }
        }

        return $terms;
    }

    /**
     * Caches one embedding vector per term (keyed by its own content hash),
     * not one entry for the whole catalog batch: a single term's vector is a
     * few KB, but a batch of a few hundred easily exceeds a SQL cache
     * driver's max packet size — and per-term caching also means adding one
     * new attribute doesn't invalidate every other term's cached embedding.
     *
     * @param  array<int, CatalogTerm>  $terms
     * @return array<int, array<int, float>>
     */
    private function embedTerms(array $terms): array
    {
        $labels = array_map(fn (CatalogTerm $term): string => "{$term->type}: {$term->label}", $terms);
        $cacheKeys = array_map(fn (string $label): string => 'products:import:term-embedding:'.md5($label), $labels);

        $cached = Cache::many($cacheKeys);

        $missingIndexes = array_values(array_filter(
            array_keys($cacheKeys),
            fn (int $index): bool => $cached[$cacheKeys[$index]] === null,
        ));

        if ($missingIndexes !== []) {
            $missingEmbeddings = $this->embedder->embed(array_map(fn (int $index): string => $labels[$index], $missingIndexes));

            foreach ($missingIndexes as $position => $index) {
                $cached[$cacheKeys[$index]] = $missingEmbeddings[$position];
                Cache::put($cacheKeys[$index], $missingEmbeddings[$position], self::CACHE_TTL_SECONDS);
            }
        }

        return array_map(fn (string $key): array => $this->toVector($cached[$key]), $cacheKeys);
    }

    /**
     * @return array<int, float>
     */
    private function toVector(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $component): float => (float) $component,
            array_filter($value, fn (mixed $component): bool => is_float($component) || is_int($component)),
        ));
    }

    /**
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $index => $value) {
            $dotProduct += $value * ($b[$index] ?? 0.0);
            $normA += $value ** 2;
        }

        foreach ($b as $value) {
            $normB += $value ** 2;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
