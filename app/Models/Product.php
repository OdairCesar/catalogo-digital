<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\ProductAgeGroup;
use App\Enums\ProductCondition;
use App\Enums\ProductGender;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property ProductCondition $condition
 * @property PageStatus $status
 * @property array<int, string>|null $images
 * @property-read Company $company
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasStoreInventoryOverride;

    /** @var list<string> */
    protected $fillable = [
        'company_id',
        'product_category_id',
        'title',
        'slug',
        'sku',
        'description',
        'brand',
        'gtin',
        'mpn',
        'condition',
        'gender',
        'age_group',
        'base_price',
        'base_sale_price',
        'base_stock',
        'weight_kg',
        'height_cm',
        'width_cm',
        'length_cm',
        'cover_image',
        'images',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'condition' => ProductCondition::class,
            'gender' => ProductGender::class,
            'age_group' => ProductAgeGroup::class,
            'status' => PageStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return HasMany<ProductInventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * @return HasMany<ProductAvailability, $this>
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(ProductAvailability::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published)
            ->whereHas('company', fn (Builder $query): Builder => $query->where('status', PageStatus::Published));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function effectivePrice(?Store $store = null): ?float
    {
        $price = $this->effectiveValue('price', $store, $this->base_price);

        return is_numeric($price) ? (float) $price : null;
    }

    public function effectiveSalePrice(?Store $store = null): ?float
    {
        $price = $this->effectiveValue('sale_price', $store, $this->base_sale_price);

        return is_numeric($price) ? (float) $price : null;
    }

    public function effectiveStock(?Store $store = null): ?int
    {
        $stock = $this->effectiveValue('quantity', $store, $this->base_stock);

        return is_numeric($stock) ? (int) $stock : null;
    }

    /**
     * The image to represent this product with when no per-variant image is
     * being shown (detail page, listing card, structured data): the cover
     * image, falling back to the first active variant's image.
     */
    public function displayImage(): ?string
    {
        if ($this->cover_image !== null) {
            return $this->cover_image;
        }

        return $this->variants
            ->first(fn (ProductVariant $variant): bool => $variant->is_active && $variant->image !== null)
            ?->image;
    }

    public function isAvailableNow(): bool
    {
        if ($this->availabilities->isEmpty()) {
            return true;
        }

        $now = Carbon::now();
        $time = $now->format('H:i:s');

        return $this->availabilities->contains(function (ProductAvailability $availability) use ($now, $time): bool {
            if ($availability->weekday !== $now->dayOfWeek) {
                return false;
            }

            if ($availability->starts_at <= $availability->ends_at) {
                return $time >= $availability->starts_at && $time <= $availability->ends_at;
            }

            return $time >= $availability->starts_at || $time <= $availability->ends_at;
        });
    }
}
