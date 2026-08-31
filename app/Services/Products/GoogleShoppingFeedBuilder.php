<?php

namespace App\Services\Products;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class GoogleShoppingFeedBuilder
{
    /**
     * Attribute names (accent/case-insensitive) mapped to the Google Shopping
     * variant dimension they represent. Attribute names that don't match stay
     * out of the formal fields but keep differentiating the variant's title.
     *
     * @var array<string, string>
     */
    private const ATTRIBUTE_NAME_MAP = [
        'cor' => 'color',
        'color' => 'color',
        'colour' => 'color',
        'tamanho' => 'size',
        'size' => 'size',
        'numeracao' => 'size',
        'material' => 'material',
        'padrao' => 'pattern',
        'estampa' => 'pattern',
        'pattern' => 'pattern',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsForCompany(Company $company): array
    {
        return Cache::remember($this->cacheKey($company), now()->addHour(), fn (): array => $this->buildItemsForCompany($company));
    }

    public function forgetCache(Company $company): void
    {
        Cache::forget($this->cacheKey($company));
    }

    private function cacheKey(Company $company): string
    {
        return "google-shopping-feed-{$company->id}";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildItemsForCompany(Company $company): array
    {
        $products = $company->products()
            ->active()
            ->with(['category', 'variants.attributeValues.attribute'])
            ->get();

        $items = [];

        foreach ($products as $product) {
            if (blank($product->brand)) {
                continue;
            }

            $additionalImageLinks = $this->additionalImageUrls($product->images);
            $activeVariants = $product->variants->where('is_active', true);

            if ($activeVariants->isEmpty()) {
                if (blank($product->cover_image)) {
                    continue;
                }

                $items[] = $this->itemForProduct($product, $additionalImageLinks);

                continue;
            }

            foreach ($activeVariants as $variant) {
                if (blank($variant->effectiveImage())) {
                    continue;
                }

                $items[] = $this->itemForVariant($product, $variant, $additionalImageLinks);
            }
        }

        return $items;
    }

    /**
     * @param  array<int, string>  $additionalImageLinks
     * @return array<string, mixed>
     */
    private function itemForProduct(Product $product, array $additionalImageLinks): array
    {
        return [
            'id' => "product-{$product->id}",
            'item_group_id' => null,
            'item_group_title' => null,
            'title' => $this->titleAttribute($product->title),
            'description' => $this->descriptionAttribute($product->description, $product->title),
            'link' => route('products.show', $product),
            'image_link' => $this->imageUrl($product->cover_image),
            'additional_image_links' => $additionalImageLinks,
            'availability' => $this->availability($product->effectiveStock()),
            'price' => $this->priceAttribute($product->effectivePrice()),
            'sale_price' => $this->priceAttribute($product->effectiveSalePrice()),
            'brand' => $product->brand,
            'condition' => $product->condition->value,
            'gtin' => $product->gtin,
            'mpn' => $product->mpn,
            'identifier_exists' => $this->identifierExists($product->gtin, $product->mpn, $product->brand),
            'google_product_category' => $product->category?->google_product_category,
            'gender' => $product->gender?->value,
            'age_group' => $product->age_group?->value,
            'color' => null,
            'size' => null,
            'material' => null,
            'pattern' => null,
            'shipping_weight' => $this->weightAttribute($product->weight_kg),
        ];
    }

    /**
     * @param  array<int, string>  $additionalImageLinks
     * @return array<string, mixed>
     */
    private function itemForVariant(Product $product, ProductVariant $variant, array $additionalImageLinks): array
    {
        $attributes = $variant->attributeLabel();
        $title = $attributes !== '' ? "{$product->title} - {$attributes}" : $product->title;

        return [
            'id' => "variant-{$variant->id}",
            'item_group_id' => "product-{$product->id}",
            'item_group_title' => $this->titleAttribute($product->title),
            'title' => $this->titleAttribute($title),
            'description' => $this->descriptionAttribute($product->description, $title),
            'link' => route('products.show', $product),
            'image_link' => $this->imageUrl($variant->effectiveImage()),
            'additional_image_links' => $additionalImageLinks,
            'availability' => $this->availability($variant->effectiveStock()),
            'price' => $this->priceAttribute($variant->effectivePrice()),
            'sale_price' => $this->priceAttribute($variant->effectiveSalePrice()),
            'brand' => $product->brand,
            'condition' => $product->condition->value,
            'gtin' => $product->gtin,
            'mpn' => $product->mpn,
            'identifier_exists' => $this->identifierExists($product->gtin, $product->mpn, $product->brand),
            'google_product_category' => $product->category?->google_product_category,
            'gender' => $product->gender?->value,
            'age_group' => $product->age_group?->value,
            ...$this->googleVariantAttributes($variant),
            'shipping_weight' => $this->weightAttribute($variant->effectiveWeightKg()),
        ];
    }

    /**
     * @return array{color: ?string, size: ?string, material: ?string, pattern: ?string}
     */
    private function googleVariantAttributes(ProductVariant $variant): array
    {
        $mapped = ['color' => null, 'size' => null, 'material' => null, 'pattern' => null];

        foreach ($variant->attributeValues as $attributeValue) {
            $normalizedName = Str::of($attributeValue->attribute->name)->ascii()->lower()->toString();
            $googleField = self::ATTRIBUTE_NAME_MAP[$normalizedName] ?? null;

            if ($googleField === null) {
                continue;
            }

            $mapped[$googleField] = $mapped[$googleField] === null
                ? $attributeValue->value
                : "{$mapped[$googleField]}/{$attributeValue->value}";
        }

        return $mapped;
    }

    private function availability(?int $stock): string
    {
        return $stock === 0 ? 'out_of_stock' : 'in_stock';
    }

    private function priceAttribute(?float $price): ?string
    {
        return $price !== null ? number_format($price, 2, '.', '').' BRL' : null;
    }

    private function weightAttribute(?float $weightKg): ?string
    {
        return $weightKg !== null ? number_format($weightKg, 2, '.', '').' kg' : null;
    }

    private function titleAttribute(string $title): string
    {
        return Str::limit($title, 150, '');
    }

    private function descriptionAttribute(?string $description, string $fallback): string
    {
        $description = $description !== null ? strip_tags($description) : $fallback;

        return Str::limit($description, 5000, '');
    }

    /**
     * Google requires either a GTIN, or a brand plus an MPN, to consider a
     * product identified. Anything short of that must be flagged explicitly
     * so the listing isn't disapproved for a missing identifier.
     */
    private function identifierExists(?string $gtin, ?string $mpn, ?string $brand): ?string
    {
        return blank($gtin) && (blank($mpn) || blank($brand)) ? 'no' : null;
    }

    /**
     * @param  array<int, string>|null  $images
     * @return array<int, string>
     */
    private function additionalImageUrls(?array $images): array
    {
        return collect($images ?? [])
            ->map(fn (string $path): string => Storage::disk('cloudinary')->url($path))
            ->all();
    }

    private function imageUrl(?string $path): ?string
    {
        return $path !== null ? Storage::disk('cloudinary')->url($path) : null;
    }
}
