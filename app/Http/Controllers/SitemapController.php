<?php

namespace App\Http\Controllers;

use App\Services\Seo\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapBuilder $sitemapBuilder) {}

    public function index(): Response
    {
        return response()
            ->view('sitemap.index', ['urls' => $this->sitemapBuilder->urls()])
            ->header('Content-Type', 'application/xml');
    }
}
