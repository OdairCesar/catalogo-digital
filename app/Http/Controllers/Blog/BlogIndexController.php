<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Services\Blog\BlogPostViewModelFactory;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\StructuredDataService;
use Illuminate\Contracts\View\View;

class BlogIndexController extends Controller
{
    public function __construct(
        private readonly BlogPostViewModelFactory $viewModelFactory,
        private readonly StructuredDataService $structuredData,
    ) {}

    public function index(?Category $category = null): View
    {
        $posts = ($category?->posts() ?? Post::query())
            ->published()
            ->with('category')
            ->latest('published_at')
            ->paginate(12)
            ->through(fn (Post $post): array => $this->viewModelFactory->teaser($post));

        $categories = Category::query()
            ->whereIn('id', Post::query()->published()->select('category_id'))
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        $breadcrumbs = BreadcrumbBuilder::start()->add('Blog', $category ? route('blog.index') : null);

        if ($category) {
            $breadcrumbs->add($category->name);
        }

        $breadcrumbs = $breadcrumbs->build();

        return view('pages.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'category' => $category,
            'breadcrumbs' => $breadcrumbs,
            'jsonLd' => [$this->structuredData->breadcrumbList($breadcrumbs)],
        ]);
    }
}
