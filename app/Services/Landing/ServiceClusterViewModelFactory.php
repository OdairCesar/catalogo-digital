<?php

namespace App\Services\Landing;

use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\ContentComposer;
use App\Services\Seo\InternalLinkService;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\LandingPageViewModel;
use App\ViewModels\ServiceViewModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final readonly class ServiceClusterViewModelFactory
{
    public function __construct(
        private ContentComposer $composer,
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
        private InternalLinkService $internalLinks,
    ) {}

    public function makeForSlug(Service $service, string $slug): ServiceViewModel|LandingPageViewModel
    {
        $cluster = ServiceCluster::query()
            ->published()
            ->where('service_id', $service->id)
            ->where('slug', $slug)
            ->first();

        if ($cluster !== null) {
            return $this->makeForCluster($cluster);
        }

        $pivot = ServiceClusterLandingPage::query()
            ->published()
            ->where('slug', $slug)
            ->whereHas('serviceCluster', fn (Builder $query): Builder => $query->where('service_id', $service->id))
            ->firstOrFail();

        return $this->makeForClusterCity($pivot);
    }

    public function makeForCluster(ServiceCluster $cluster): ServiceViewModel
    {
        $cluster->loadMissing('service');
        $service = $cluster->service;
        $clusterTitle = $this->composer->compose((string) $cluster->title);

        $breadcrumbs = BreadcrumbBuilder::start()
            ->add('Serviços', route('services.index'))
            ->add($service->title, route('services.show', $service))
            ->add($clusterTitle)
            ->build();

        $subtitle = $this->composer->compose((string) $cluster->subtitle);
        $faq = $this->composer->composeFaq($cluster->faq ?? []);

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->serviceGeneric($service, $subtitle),
            $this->structuredData->faqPage($faq),
            $this->structuredData->breadcrumbList($breadcrumbs),
        );

        $heroImagePath = $cluster->hero_image ?? $service->hero_image;

        return new ServiceViewModel(
            title: $clusterTitle,
            subtitle: $subtitle,
            description: $this->composer->compose((string) $cluster->description),
            benefits: $this->composer->composeList($cluster->benefits ?? []),
            faq: $faq,
            seo: $this->seoMetaBuilder->forServiceCluster($cluster),
            heroImageUrl: $heroImagePath ? Storage::disk('cloudinary')->url($heroImagePath) : null,
            breadcrumbs: $breadcrumbs,
            relatedLinks: $this->internalLinks->otherClustersForService($cluster),
            jsonLd: $jsonLd,
        );
    }

    public function makeForClusterCity(ServiceClusterLandingPage $pivot): LandingPageViewModel
    {
        $pivot->loadMissing('serviceCluster.service', 'city');

        $data = Cache::remember(
            self::cacheKey((string) $pivot->slug),
            now()->addHour(),
            fn (): array => $this->buildForClusterCity($pivot)->toArray(),
        );

        return LandingPageViewModel::fromArray($data);
    }

    public static function cacheKey(string $slug): string
    {
        return "service-cluster-landing-page-view-model:{$slug}";
    }

    private function buildForClusterCity(ServiceClusterLandingPage $pivot): LandingPageViewModel
    {
        $cluster = $pivot->serviceCluster;
        $service = $cluster->service;
        $city = $pivot->city;
        $rawClusterTitle = (string) $cluster->title;
        $clusterTitle = $this->composer->compose($rawClusterTitle, $city);
        $defaultH1 = $this->composer->hasLocationTokens($rawClusterTitle) ? $clusterTitle : "{$clusterTitle} em {$city->name}";

        $benefits = $this->composer->composeList($cluster->benefits ?? [], $city);
        $faq = $this->composer->composeFaq($cluster->faq ?? [], $city);

        $breadcrumbs = BreadcrumbBuilder::start()
            ->add($service->title, route('services.show', $service))
            ->add($clusterTitle, route('services.clusters.show', [$service, $cluster]))
            ->add($city->name)
            ->build();

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->serviceForClusterCity($pivot),
            $this->structuredData->faqPage($faq),
            $this->structuredData->breadcrumbList($breadcrumbs),
        );

        return new LandingPageViewModel(
            h1: $this->composer->compose($pivot->custom_h1 ?? $defaultH1, $city),
            subtitle: $this->composer->compose($pivot->custom_subtitle ?? (string) $cluster->subtitle, $city),
            intro: $this->composer->compose($pivot->custom_intro ?? (string) $cluster->description, $city),
            benefits: $benefits,
            faq: $faq,
            ctaLabel: $this->composer->compose($pivot->custom_cta ?? 'Solicitar orçamento', $city),
            seo: $this->seoMetaBuilder->forServiceClusterLandingPage($pivot),
            breadcrumbs: $breadcrumbs,
            relatedLinks: $this->internalLinks->relatedLinksForCluster($pivot),
            jsonLd: $jsonLd,
        );
    }
}
