<?php

namespace App\Services\Seo;

use App\Models\City;
use App\Models\Company;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\Section;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Models\State;
use App\ViewModels\SeoMeta;
use Illuminate\Support\Str;

final readonly class SeoMetaBuilder
{
    public function __construct(private ContentComposer $composer) {}

    public function forLandingPage(LandingPage $landingPage): SeoMeta
    {
        $service = $landingPage->service;
        $city = $landingPage->city;

        $description = $landingPage->meta_description !== null
            ? $this->composer->compose($landingPage->meta_description, $city)
            : Str::limit($this->composer->compose($service->description, $city), 155);

        return new SeoMeta(
            title: $this->composer->compose($landingPage->meta_title ?? "{$service->title} em {$city->name} | {$this->siteName()}", $city),
            description: $description,
            canonical: $landingPage->canonical ?? route('landing.show', $landingPage),
            robots: $landingPage->robots ?? 'index,follow',
        );
    }

    public function forService(Service $service): SeoMeta
    {
        return new SeoMeta(
            title: "{$service->title} — {$this->siteName()}",
            description: Str::limit($this->composer->compose($service->description), 155),
            canonical: route('services.show', $service),
        );
    }

    public function forServiceCluster(ServiceCluster $cluster): SeoMeta
    {
        $description = $cluster->meta_description !== null
            ? $this->composer->compose($cluster->meta_description)
            : Str::limit($this->composer->compose((string) $cluster->description), 155);

        return new SeoMeta(
            title: $this->composer->compose($cluster->meta_title ?? "{$cluster->title} — {$this->siteName()}"),
            description: $description,
            canonical: $cluster->canonical ?? route('services.clusters.show', [$cluster->service, $cluster]),
            robots: $cluster->robots ?? 'index,follow',
        );
    }

    public function forServiceClusterLandingPage(ServiceClusterLandingPage $pivot): SeoMeta
    {
        $cluster = $pivot->serviceCluster;
        $city = $pivot->city;

        $description = $pivot->meta_description !== null
            ? $this->composer->compose($pivot->meta_description, $city)
            : Str::limit($this->composer->compose((string) $cluster->description, $city), 155);

        $defaultTitle = $this->composer->hasLocationTokens((string) $cluster->title)
            ? "{$cluster->title} | {$this->siteName()}"
            : "{$cluster->title} em {$city->name} | {$this->siteName()}";

        return new SeoMeta(
            title: $this->composer->compose($pivot->meta_title ?? $defaultTitle, $city),
            description: $description,
            canonical: $pivot->canonical ?? route('services.clusters.show', [$cluster->service, $pivot->slug]),
            robots: $pivot->robots ?? 'index,follow',
        );
    }

    public function forCity(City $city): SeoMeta
    {
        return new SeoMeta(
            title: "Tecnologia em {$city->name}/{$city->state?->uf} — {$this->siteName()}",
            description: Str::limit($city->intro, 155),
            canonical: route('cities.show', $city),
            robots: 'index,follow',
        );
    }

    public function forState(State $state): SeoMeta
    {
        return new SeoMeta(
            title: $state->meta_title ?? "Tecnologia em {$state->name} — {$this->siteName()}",
            description: $state->meta_description ?? Str::limit($state->intro, 155),
            canonical: $state->canonical ?? route('states.show', $state),
            robots: $state->robots ?? 'index,follow',
        );
    }

    public function forPortfolioItem(Section $portfolioItem): SeoMeta
    {
        return new SeoMeta(
            title: $portfolioItem->meta_title ?? "{$portfolioItem->title} — Portfólio {$this->siteName()}",
            description: $portfolioItem->meta_description ?? Str::limit($portfolioItem->excerpt ?? '', 155),
            canonical: $portfolioItem->canonical ?? route('portfolio.show', $portfolioItem->slug ?? ''),
            robots: $portfolioItem->robots ?? 'index,follow',
        );
    }

    public function forProduct(Product $product): SeoMeta
    {
        return new SeoMeta(
            title: "{$product->title} — {$product->company->name}",
            description: Str::limit(strip_tags((string) $product->description), 155),
            canonical: route('products.show', $product),
        );
    }

    public function forPost(Post $post): SeoMeta
    {
        return new SeoMeta(
            title: $post->meta_title ?? "{$post->title} — Blog {$this->siteName()}",
            description: $post->meta_description ?? Str::limit(strip_tags($post->excerpt ?? ''), 155),
            canonical: $post->canonical ?? route('blog.show', $post),
            robots: $post->robots ?? 'index,follow',
        );
    }

    private function siteName(): string
    {
        return Company::siteName();
    }
}
