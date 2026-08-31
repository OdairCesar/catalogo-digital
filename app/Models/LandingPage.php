<?php

namespace App\Models;

use App\Enums\PageStatus;
use Database\Factories\LandingPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property PageStatus $status
 * @property-read Service $service
 * @property-read City $city
 */
class LandingPage extends Model
{
    /** @use HasFactory<LandingPageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'service_id',
        'city_id',
        'slug',
        'meta_title',
        'meta_description',
        'canonical',
        'robots',
        'custom_h1',
        'custom_subtitle',
        'custom_intro',
        'custom_cta',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @param  Builder<LandingPage>  $query
     * @return Builder<LandingPage>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published)
            ->whereHas('service', fn (Builder $query): Builder => $query->where('status', PageStatus::Published))
            ->whereHas('city', fn (Builder $query): Builder => $query->where('status', PageStatus::Published));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
