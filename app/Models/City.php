<?php

namespace App\Models;

use App\Enums\PageStatus;
use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property PageStatus $status
 */
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'state_id',
        'region',
        'population',
        'gdp',
        'latitude',
        'longitude',
        'intro',
        'business_text',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'population' => 'integer',
            'gdp' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => PageStatus::class,
        ];
    }

    /**
     * @return HasMany<LandingPage, $this>
     */
    public function landingPages(): HasMany
    {
        return $this->hasMany(LandingPage::class);
    }

    /**
     * @return HasMany<ServiceClusterLandingPage, $this>
     */
    public function clusterLandingPages(): HasMany
    {
        return $this->hasMany(ServiceClusterLandingPage::class);
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * @param  Builder<City>  $query
     * @return Builder<City>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published);
    }

    /**
     * @param  Builder<City>  $query
     * @return Builder<City>
     */
    public function scopeSameRegionAs(Builder $query, self $city): Builder
    {
        return $query->where('region', $city->region)->whereKeyNot($city->getKey());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
