<?php

namespace App\Services\Blog;

use App\Models\Post;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\BlogPostViewModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final readonly class BlogPostViewModelFactory
{
    public function __construct(
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
    ) {}

    public function makeShow(Post $post): BlogPostViewModel
    {
        // A Post reaching this factory is always route-bound via the `published()` scope,
        // which guarantees a title/slug were already filled in by the AI generation job.
        $title = (string) $post->title;

        $breadcrumbs = BreadcrumbBuilder::start()->add('Blog', route('blog.index'));

        if ($post->category) {
            $breadcrumbs->add($post->category->name, route('blog.category', $post->category));
        }

        $breadcrumbs = $breadcrumbs->add($title)->build();

        $jsonLd = [
            $this->structuredData->blogPosting($post),
            $this->structuredData->breadcrumbList($breadcrumbs),
        ];

        $relatedPosts = Post::query()
            ->published()
            ->when($post->category_id, fn (Builder $query): Builder => $query->where('category_id', $post->category_id))
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Post $related): array => $this->teaser($related))
            ->all();

        return new BlogPostViewModel(
            title: $title,
            excerpt: $post->excerpt ?? '',
            contentHtml: $post->content ?? '',
            coverImageUrl: $this->coverImageUrl($post),
            publishedAt: $post->published_at,
            categoryLabel: $post->category?->name,
            categoryUrl: $post->category ? route('blog.category', $post->category) : null,
            tags: $post->tags ?? [],
            authorName: $post->author?->name,
            relatedPosts: $relatedPosts,
            seo: $this->seoMetaBuilder->forPost($post),
            breadcrumbs: $breadcrumbs,
            jsonLd: $jsonLd,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function teaser(Post $post): array
    {
        return [
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'url' => route('blog.show', $post),
            'coverImageUrl' => $this->coverImageUrl($post),
            'categoryLabel' => $post->category?->name,
            'publishedAt' => $post->published_at,
        ];
    }

    private function coverImageUrl(Post $post): ?string
    {
        return $post->cover_image ? Storage::disk('cloudinary')->url($post->cover_image) : null;
    }
}
