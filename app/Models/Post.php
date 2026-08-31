<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property array<int, string>|null $tags
 * @property array<string, mixed>|null $ai_brief
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property-read Category|null $category
 * @property-read User|null $author
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'tags',
        'status',
        'ai_brief',
        'ai_model',
        'ai_error',
        'meta_title',
        'meta_description',
        'canonical',
        'robots',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'ai_brief' => 'array',
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
