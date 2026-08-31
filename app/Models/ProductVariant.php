<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Product $product
 */
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    use HasStoreInventoryOverride;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock',
        'weight_kg',
        'height_cm',
        'width_cm',
        'length_cm',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<ProductInventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * @return BelongsToMany<ProductAttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attribute_values');
    }

    public function effectiveImage(): ?string
    {
        return $this->image ?? $this->product->cover_image;
    }

    /**
     * Comma-separated list of this variant's attribute values (e.g. "Azul, M"),
     * used to differentiate its label/title wherever it's displayed.
     */
    public function attributeLabel(): string
    {
        return $this->attributeValues->map(fn (ProductAttributeValue $value): string => $value->value)->implode(', ');
    }

    public function effectiveWeightKg(): ?float
    {
        return $this->effectiveDimension('weight_kg');
    }

    public function effectiveHeightCm(): ?float
    {
        return $this->effectiveDimension('height_cm');
    }

    public function effectiveWidthCm(): ?float
    {
        return $this->effectiveDimension('width_cm');
    }

    public function effectiveLengthCm(): ?float
    {
        return $this->effectiveDimension('length_cm');
    }

    public function effectivePrice(?Store $store = null): ?float
    {
        $price = $this->effectiveValue('price', $store, $this->price, $this->product->base_price);

        return is_numeric($price) ? (float) $price : null;
    }

    public function effectiveSalePrice(?Store $store = null): ?float
    {
        $price = $this->effectiveValue('sale_price', $store, $this->sale_price, $this->product->base_sale_price);

        return is_numeric($price) ? (float) $price : null;
    }

    public function effectiveStock(?Store $store = null): ?int
    {
        $stock = $this->effectiveValue('quantity', $store, $this->stock, $this->product->base_stock);

        return is_numeric($stock) ? (int) $stock : null;
    }

    private function effectiveDimension(string $column): ?float
    {
        $value = $this->{$column} ?? $this->product->{$column};

        return is_numeric($value) ? (float) $value : null;
    }
}
