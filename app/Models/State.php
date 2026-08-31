<?php

namespace App\Models;

use App\Enums\PageStatus;
use Database\Factories\StateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property PageStatus $status
 */
class State extends Model
{
    /** @use HasFactory<StateFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'uf',
        'intro',
        'business_text',
        'meta_title',
        'meta_description',
        'canonical',
        'robots',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
        ];
    }

    /**
     * @return HasMany<City, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * @param  Builder<State>  $query
     * @return Builder<State>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
