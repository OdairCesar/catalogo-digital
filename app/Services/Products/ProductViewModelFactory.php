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
    /** @var array<string, string> */
    private const COLOR_HEX = [
        'roxo' => '#9647B2',
        'preto' => '#1C2839',
        'cinza-azulado' => '#5B6B82',
        'lilás' => '#E9D6F0',
        'lilas' => '#E9D6F0',
        'branco' => '#FAFAFC',
    ];

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
        $selector = $this->buildTwoAxisSelector($activeVariants);

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
     * seen in the Fit By Cae design) when every variant carries exactly one
     * value for each of those two attributes. Products whose variants use a
     * different attribute shape simply get an empty selector here — the
     * caller falls back to the flat variant list in that case.
     *
     * @param  Collection<int, ProductVariant>  $variants
     * @return array{
     *     sizes: list<array{label: string}>,
     *     colors: list<array{label: string, hex: string}>,
     *     matrix: array<string, array{priceLabel: ?string, stockLabel: string, imageUrl: ?string, inStock: bool}>,
     * }
     */
    private function buildTwoAxisSelector(Collection $variants): array
    {
        $sizes = [];
        $colors = [];
        $matrix = [];

        foreach ($variants as $variant) {
            $size = null;
            $color = null;

            foreach ($variant->attributeValues as $value) {
                /** @var ProductAttributeValue $value */
                $attributeName = mb_strtolower($value->attribute->name);

                if ($attributeName === 'tamanho') {
                    $size = $value->value;
                } elseif ($attributeName === 'cor') {
                    $color = $value->value;
                }
            }

            if ($size === null || $color === null) {
                continue;
            }

            $sizes[$size] ??= true;
            $colors[$color] ??= self::COLOR_HEX[mb_strtolower($color)] ?? self::COLOR_HEX_DEFAULT;

            $matrix["{$size}|{$color}"] = [
                'priceLabel' => $this->priceLabel($variant->effectivePrice()),
                'stockLabel' => $this->stockLabel($variant->effectiveStock()),
                'imageUrl' => $this->imageUrl($variant->effectiveImage()),
                'inStock' => $variant->effectiveStock() !== 0,
            ];
        }

        return [
            'sizes' => array_map(fn (string $label): array => ['label' => $label], array_keys($sizes)),
            'colors' => array_map(fn (string $label, string $hex): array => ['label' => $label, 'hex' => $hex], array_keys($colors), array_values($colors)),
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
