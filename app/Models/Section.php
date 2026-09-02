<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property SectionType $type
 * @property array<string, mixed>|null $data
 * @property list<array{label: string, value: string}>|null $extra_fields
 * @property PageStatus $status
 */
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'type',
        'service_id',
        'product_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'meta_title',
        'meta_description',
        'canonical',
        'robots',
        'status',
        'sort_order',
        'data',
        'extra_fields',
    ];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'status' => PageStatus::class,
            'data' => 'array',
            'extra_fields' => 'array',
        ];
    }

    /**
     * The single record for a singleton section type (e.g. the home hero),
     * cached forever like `Company::current()` and busted by `SectionObserver`.
     */
    public static function block(SectionType $type): ?self
    {
        $attributes = Cache::rememberForever(
            self::cacheKey($type),
            fn (): ?array => self::query()->ofType($type)->oldest('id')->first()?->getAttributes(),
        );

        return $attributes !== null ? (new self)->newFromBuilder($attributes) : null;
    }

    public static function cacheKey(SectionType $type): string
    {
        return "section.block.{$type->value}";
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @param  Builder<Section>  $query
     * @return Builder<Section>
     */
    public function scopeOfType(Builder $query, SectionType $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @param  Builder<Section>  $query
     * @return Builder<Section>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published);
    }

    /**
     * @param  Builder<Section>  $query
     * @return Builder<Section>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
