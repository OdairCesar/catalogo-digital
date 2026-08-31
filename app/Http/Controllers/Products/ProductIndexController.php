<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Services\Products\ProductViewModelFactory;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\StructuredDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductIndexController extends Controller
{
    public function __construct(
        private readonly ProductViewModelFactory $viewModelFactory,
        private readonly StructuredDataService $structuredData,
    ) {}

    public function index(Request $request, ?ProductCategory $productCategory = null): View
    {
        $priceMin = $this->numericQuery($request, 'preco_min');
        $priceMax = $this->numericQuery($request, 'preco_max');
        $attributeValueIds = collect($request->query('atributos', []))
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values();

        $items = Product::query()
            ->active()
            ->with(['company', 'category', 'variants.attributeValues.attribute'])
            ->when($productCategory, fn (Builder $query, ProductCategory $productCategory): Builder => $query->where('product_category_id', $productCategory->id))
            ->when($priceMin !== null, fn (Builder $query): Builder => $query->where('base_price', '>=', $priceMin))
            ->when($priceMax !== null, fn (Builder $query): Builder => $query->where('base_price', '<=', $priceMax))
            ->when($attributeValueIds->isNotEmpty(), fn (Builder $query): Builder => $this->filterByAttributeValues($query, $attributeValueIds))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product): array => $this->viewModelFactory->teaser($product));

        $categories = ProductCategory::query()
            ->whereIn('id', Product::query()->active()->select('product_category_id'))
            ->orderBy('name')
            ->get();

        $breadcrumbs = BreadcrumbBuilder::start()->add('Produtos', $productCategory ? route('products.index') : null);

        if ($productCategory) {
            $breadcrumbs->add($productCategory->name);
        }

        $breadcrumbs = $breadcrumbs->build();

        return view('pages.products.index', [
            'items' => $items,
            'categories' => $categories,
            'productCategory' => $productCategory,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'selectedAttributeValueIds' => $attributeValueIds->all(),
            'breadcrumbs' => $breadcrumbs,
            'jsonLd' => [$this->structuredData->breadcrumbList($breadcrumbs)],
            'whatsappLink' => Company::current()?->whatsappLink('Oi Cae! Não sei qual tamanho pegar, pode me ajudar? 💜'),
        ]);
    }

    private function numericQuery(Request $request, string $key): ?float
    {
        $value = $request->query($key);

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  Builder<Product>  $query
     * @param  Collection<int, int>  $attributeValueIds
     * @return Builder<Product>
     */
    private function filterByAttributeValues(Builder $query, Collection $attributeValueIds): Builder
    {
        $groups = ProductAttributeValue::query()
            ->whereIn('id', $attributeValueIds)
            ->get()
            ->groupBy('product_attribute_id');

        foreach ($groups as $valuesInGroup) {
            $ids = $valuesInGroup->pluck('id');

            $query->whereHas('variants.attributeValues', fn (Builder $query): Builder => $query->whereIn('product_attribute_values.id', $ids));
        }

        return $query;
    }
}
