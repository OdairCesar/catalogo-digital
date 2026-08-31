<?php

namespace App\Services\Seo;

use App\Enums\SectionType;
use App\Enums\SiteModule;
use App\Models\Category;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Section;
use App\Models\SectionTypeSetting;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Models\State;
use App\Services\Tools\ToolRegistry;
use Illuminate\Support\Facades\Cache;

final class SitemapBuilder
{
    public function __construct(private readonly ToolRegistry $toolRegistry) {}

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    public function urls(): array
    {
        return Cache::remember(self::cacheKey(), now()->addHour(), fn (): array => $this->build());
    }

    public static function cacheKey(): string
    {
        return 'sitemap-urls';
    }

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    private function build(): array
    {
        $urls = [
            $this->url(route('home')),
            $this->url(route('contact.show')),
        ];

        if (SectionTypeSetting::isEnabled(SectionType::About)) {
            $urls[] = $this->url(route('about'));
        }

        if (SectionTypeSetting::isEnabled(SectionType::FaqGroup)) {
            $urls[] = $this->url(route('faq.index'));
        }

        if (SiteModule::Servicos->isEnabled()) {
            array_push($urls, ...$this->servicosUrls());
        }

        if (SiteModule::Blog->isEnabled()) {
            array_push($urls, ...$this->blogUrls());
        }

        if (SiteModule::Produtos->isEnabled()) {
            array_push($urls, ...$this->produtosUrls());
        }

        if (SectionTypeSetting::isEnabled(SectionType::Portfolio)) {
            array_push($urls, ...$this->portfolioUrls());
        }

        if (SiteModule::Ferramentas->isEnabled()) {
            array_push($urls, ...$this->ferramentasUrls());
        }

        return $urls;
    }

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    private function servicosUrls(): array
    {
        $urls = [
            $this->url(route('services.index')),
            $this->url(route('cities.index')),
            $this->url(route('states.index')),
        ];

        foreach (Service::query()->active()->get() as $service) {
            $urls[] = $this->url(route('services.show', $service), $service->updated_at?->toAtomString());
        }

        foreach (ServiceCluster::query()->published()->with('service')->get() as $cluster) {
            $urls[] = $this->url(route('services.clusters.show', [$cluster->service, $cluster]), $cluster->updated_at?->toAtomString());
        }

        foreach (ServiceClusterLandingPage::query()->published()->with('serviceCluster.service')->get() as $clusterLandingPage) {
            $urls[] = $this->url(
                route('services.clusters.show', [$clusterLandingPage->serviceCluster->service, $clusterLandingPage->slug]),
                $clusterLandingPage->updated_at?->toAtomString(),
            );
        }

        foreach (City::query()->active()->get() as $city) {
            $urls[] = $this->url(route('cities.show', $city), $city->updated_at?->toAtomString());
        }

        foreach (State::query()->active()->get() as $state) {
            $urls[] = $this->url(route('states.show', $state), $state->updated_at?->toAtomString());
        }

        foreach (LandingPage::query()->published()->get() as $landingPage) {
            $urls[] = $this->url(route('landing.show', $landingPage), $landingPage->updated_at?->toAtomString());
        }

        return $urls;
    }

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    private function blogUrls(): array
    {
        $urls = [$this->url(route('blog.index'))];

        foreach (Post::query()->published()->get() as $post) {
            $urls[] = $this->url(route('blog.show', $post), $post->updated_at?->toAtomString());
        }

        foreach (Category::query()->whereIn('id', Post::query()->published()->select('category_id'))->get() as $category) {
            $urls[] = $this->url(route('blog.category', $category));
        }

        return $urls;
    }

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    private function produtosUrls(): array
    {
        $urls = [$this->url(route('products.index'))];

        foreach (Product::query()->active()->get() as $product) {
            $urls[] = $this->url(route('products.show', $product), $product->updated_at?->toAtomString());
        }

        foreach (ProductCategory::query()->whereIn('id', Product::query()->active()->select('product_category_id'))->get() as $productCategory) {
            $urls[] = $this->url(route('products.category', $productCategory));
        }

        return $urls;
    }

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    private function portfolioUrls(): array
    {
        $urls = [$this->url(route('portfolio.index'))];

        foreach (Section::query()->ofType(SectionType::Portfolio)->active()->whereNotNull('slug')->get() as $portfolioItem) {
            $urls[] = $this->url(route('portfolio.show', $portfolioItem->slug), $portfolioItem->updated_at?->toAtomString());
        }

        return $urls;
    }

    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    private function ferramentasUrls(): array
    {
        $urls = [$this->url(route('tools.index'))];

        foreach ($this->toolRegistry->all() as $tool) {
            $urls[] = $this->url(route('tools.show', $tool->slug));
        }

        return $urls;
    }

    /**
     * @return array{url: string, lastmod: ?string}
     */
    private function url(string $url, ?string $lastmod = null): array
    {
        return ['url' => $url, 'lastmod' => $lastmod];
    }
}
