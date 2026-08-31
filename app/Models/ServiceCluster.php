<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\ServiceClusterStatus;
use Database\Factories\ServiceClusterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<int, string>|null $benefits
 * @property array<int, array{question: string, answer: string}>|null $faq
 * @property array<int, string>|null $keywords
 * @property array<string, mixed>|null $ai_brief
 * @property ServiceClusterStatus $status
 * @property-read Service $service
 */
class ServiceCluster extends Model
{
    /** @use HasFactory<ServiceClusterFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'service_id',
        'slug',
        'name',
        'title',
        'subtitle',
        'description',
        'benefits',
        'faq',
        'keywords',
        'hero_image',
        'meta_title',
        'meta_description',
        'canonical',
        'robots',
        'status',
        'ai_brief',
        'ai_model',
        'ai_error',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'faq' => 'array',
            'keywords' => 'array',
            'ai_brief' => 'array',
            'status' => ServiceClusterStatus::class,
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
     * @return HasMany<ServiceClusterLandingPage, $this>
     */
    public function landingPages(): HasMany
    {
        return $this->hasMany(ServiceClusterLandingPage::class);
    }

    /**
     * @param  Builder<ServiceCluster>  $query
     * @return Builder<ServiceCluster>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ServiceClusterStatus::Published)
            ->whereHas('service', fn (Builder $query): Builder => $query->where('status', PageStatus::Published));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
