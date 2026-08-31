<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\ServiceClusterStatus;
use Database\Factories\ServiceClusterLandingPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property PageStatus $status
 * @property-read ServiceCluster $serviceCluster
 * @property-read City $city
 */
class ServiceClusterLandingPage extends Model
{
    /** @use HasFactory<ServiceClusterLandingPageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'service_cluster_id',
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
     * @return BelongsTo<ServiceCluster, $this>
     */
    public function serviceCluster(): BelongsTo
    {
        return $this->belongsTo(ServiceCluster::class);
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @param  Builder<ServiceClusterLandingPage>  $query
     * @return Builder<ServiceClusterLandingPage>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published)
            ->whereHas('serviceCluster', fn (Builder $query): Builder => $query
                ->where('status', ServiceClusterStatus::Published)
                ->whereHas('service', fn (Builder $query): Builder => $query->where('status', PageStatus::Published)))
            ->whereHas('city', fn (Builder $query): Builder => $query->where('status', PageStatus::Published));
    }
}
