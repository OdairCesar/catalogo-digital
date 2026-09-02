<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\ProductViewModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

final readonly class ProductViewModelFactory
{
    private const COLOR_HEX_DEFAULT = '#C9C9C9';

    public function __construct(
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
    ) {}

    public function makeShow(Product $product): ProductViewModel
    {
        $product->loadMissing(['category', 'company', 'variants.attributeValues.attribute']);
        $activeVariants = $product->variants->where('is_active', true)->values();
        $activeVariants->each(fn (ProductVariant $variant) => $variant->setRelation('product', $product));

        $breadcrumbs = BreadcrumbBuilder::start()->add('Produtos', route('products.index'));

        if ($product->category !== null) {
            $breadcrumbs->add($product->category->name, route('products.category', $product->category));
        }

        $breadcrumbs = $breadcrumbs->add($product->title)->build();

        $jsonLd = [
            $this->structuredData->product($product),
            $this->structuredData->breadcrumbList($breadcrumbs),
        ];

        $price = $product->effectivePrice();
        $stock = $product->effectiveStock();
        $selector = $this->buildColorSelector($activeVariants);

        $related = Product::query()
            ->active()
            ->with('variants')
            ->where('id', '!=', $product->id)
            ->when($product->product_category_id, fn ($query) => $query->where('product_category_id', $product->product_category_id))
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Product $related): array => [
                'title' => $related->title,
                'url' => route('products.show', $related),
                'coverImageUrl' => $this->imageUrl($related->displayImage()),
                'priceLabel' => $this->priceLabel($related->effectivePrice()),
            ])
            ->all();

        $related = array_values($related);

        return new ProductViewModel(
            title: $product->title,
            description: $product->description,
            price: $price,
            priceLabel: $this->priceLabel($price),
            salePriceLabel: $this->priceLabel($product->effectiveSalePrice()),
            installmentLabel: $this->installmentLabel($price),
            stockLabel: $this->stockLabel($stock),
            availability: $stock === 0 ? 'out of stock' : 'in stock',
            coverImageUrl: $this->imageUrl($product->displayImage()),
            galleryImageUrls: collect($product->images ?? [])->map(fn (string $path): string => Storage::disk('cloudinary')->url($path))->all(),
            brand: $product->brand,
            conditionLabel: $product->condition->getLabel(),
            categoryName: $product->category?->name,
            variants: $activeVariants->map(fn (ProductVariant $variant): array => $this->variantSummary($variant))->values()->all(),
            sizeOptions: $selector['sizes'],
            colorOptions: $selector['colors'],
            variantMatrix: $selector['matrix'],
            relatedProducts: $related,
            seo: $this->seoMetaBuilder->forProduct($product),
            breadcrumbs: $breadcrumbs,
            jsonLd: $jsonLd,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function teaser(Product $product): array
    {
        $price = $product->effectivePrice();

        return [
            'title' => $product->title,
            'excerpt' => $product->description !== null ? str($product->description)->stripTags()->limit(120)->toString() : null,
            'url' => route('products.show', $product),
            'coverImageUrl' => $this->imageUrl($product->displayImage()),
            'priceLabel' => $this->priceLabel($price),
            'installmentLabel' => $this->installmentLabel($price),
            'stockLabel' => $this->stockLabel($product->effectiveStock()),
            'tag' => $product->category?->name,
            'gradeLabel' => $this->sizeRangeLabel($product->variants),
        ];
    }

    /**
     * @return array{label: string, priceLabel: ?string, stockLabel: string, imageUrl: ?string, inStock: bool}
     */
    private function variantSummary(ProductVariant $variant): array
    {
        $attributes = $variant->attributeLabel();
        $stock = $variant->effectiveStock();

        return [
            'label' => $attributes !== '' ? $attributes : 'Variação',
            'priceLabel' => $this->priceLabel($variant->effectivePrice()),
            'stockLabel' => $this->stockLabel($stock),
            'imageUrl' => $this->imageUrl($variant->effectiveImage()),
            'inStock' => $stock !== 0,
        ];
    }

    /**
     * Splits active variants into independent "Tamanho"/"Cor" selectors (as
     * seen in the Fit By Cae design). When every variant carries a "Cor" but
     * none carries a "Tamanho", the size axis is simply left empty and the
     * matrix is keyed by color alone — the color swatches still render on
     * their own. Products whose variants use a different attribute shape
     * (e.g. size without color) get empty selectors here — the caller falls
     * back to the flat variant list in that case.
     *
     * @param  Collection<int, ProductVariant>  $variants
     * @return array{
     *     sizes: list<array{label: string}>,
     *     colors: list<array{label: string, hex: string, imageUrl: ?string}>,
     *     matrix: array<string, array{priceLabel: ?string, stockLabel: string, imageUrl: ?string, inStock: bool}>,
     * }
     */
    private function buildColorSelector(Collection $variants): array
    {
        $hasSizeAxis = $variants->contains(
            fn (ProductVariant $variant): bool => $variant->attributeValues
                ->contains(fn (ProductAttributeValue $value): bool => mb_strtolower($value->attribute->name) === 'tamanho')
        );

        $sizes = [];
        $colors = [];
        $matrix = [];

        foreach ($variants as $variant) {
            $size = null;
            $color = null;
            $colorValue = null;

            foreach ($variant->attributeValues as $value) {
                /** @var ProductAttributeValue $value */
                $attributeName = mb_strtolower($value->attribute->name);

                if ($attributeName === 'tamanho') {
                    $size = $value->value;
                } elseif ($attributeName === 'cor') {
                    $color = $value->value;
                    $colorValue = $value;
                }
            }

            if ($color === null || $colorValue === null || ($hasSizeAxis && $size === null)) {
                continue;
            }

            if ($size !== null) {
                $sizes[$size] ??= true;
            }

            $colors[$color] ??= [
                'hex' => $colorValue->hex ?? self::COLOR_HEX_DEFAULT,
                'imageUrl' => $colorValue->imageUrl(),
            ];

            $key = $hasSizeAxis ? "{$size}|{$color}" : $color;

            $matrix[$key] = [
                'priceLabel' => $this->priceLabel($variant->effectivePrice()),
                'stockLabel' => $this->stockLabel($variant->effectiveStock()),
                'imageUrl' => $this->imageUrl($variant->effectiveImage()),
                'inStock' => $variant->effectiveStock() !== 0,
            ];
        }

        return [
            'sizes' => array_map(fn (string $label): array => ['label' => $label], array_keys($sizes)),
            'colors' => array_map(
                fn (string $label, array $data): array => ['label' => $label, 'hex' => $data['hex'], 'imageUrl' => $data['imageUrl']],
                array_keys($colors),
                array_values($colors),
            ),
            'matrix' => $matrix,
        ];
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     */
    private function sizeRangeLabel(Collection $variants): ?string
    {
        $sizes = $variants
            ->flatMap(fn (ProductVariant $variant): Collection => $variant->attributeValues)
            ->filter(fn (ProductAttributeValue $value): bool => mb_strtolower($value->attribute->name) === 'tamanho')
            ->map(fn (ProductAttributeValue $value): string => $value->value)
            ->unique()
            ->values();

        return match (true) {
            $sizes->isEmpty() => null,
            $sizes->count() === 1 => $sizes->first(),
            default => "{$sizes->first()} ao {$sizes->last()}",
        };
    }

    private function installmentLabel(?float $price): ?string
    {
        if ($price === null || $price <= 0) {
            return null;
        }

        $installments = $price < 100 ? 2 : 3;
        $installmentPrice = $this->priceLabel($price / $installments);

        return $installmentPrice !== null ? "{$installments}x de {$installmentPrice} sem juros" : null;
    }

    private function priceLabel(?float $price): ?string
    {
        if ($price === null) {
            return null;
        }

        $formatted = Number::currency($price, in: 'BRL', locale: 'pt_BR');

        return $formatted !== false ? $formatted : null;
    }

    private function stockLabel(?int $stock): string
    {
        return $stock === 0 ? 'Fora de estoque' : 'Em estoque';
    }

    private function imageUrl(?string $path): ?string
    {
        return $path !== null ? Storage::disk('cloudinary')->url($path) : null;
    }
}
