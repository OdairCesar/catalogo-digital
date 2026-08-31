<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Post;
use App\Services\Blog\BlogPostViewModelFactory;
use Illuminate\Contracts\View\View;

class BlogShowController extends Controller
{
    public function __construct(private readonly BlogPostViewModelFactory $viewModelFactory) {}

    public function show(Post $post): View
    {
        return view('pages.blog.show', [
            'vm' => $this->viewModelFactory->makeShow($post),
            'whatsappLink' => Company::current()?->whatsappLink('Oi Cae! Li um post do blog e quero saber mais 💜'),
        ]);
    }
}
