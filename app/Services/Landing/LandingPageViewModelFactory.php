<?php

namespace App\Services\Landing;

use App\Models\LandingPage;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\ContentComposer;
use App\Services\Seo\InternalLinkService;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\LandingPageViewModel;
use Illuminate\Support\Facades\Cache;

final readonly class LandingPageViewModelFactory
{
    public function __construct(
        private ContentComposer $composer,
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
        private InternalLinkService $internalLinks,
    ) {}

    public function make(LandingPage $landingPage): LandingPageViewModel
    {
        $landingPage->loadMissing('service', 'city');

        $data = Cache::remember(
            self::cacheKey($landingPage->slug),
            now()->addHour(),
            fn (): array => $this->build($landingPage)->toArray(),
        );

        return LandingPageViewModel::fromArray($data);
    }

    public static function cacheKey(string $slug): string
    {
        return "landing-page-view-model:{$slug}";
    }

    private function build(LandingPage $landingPage): LandingPageViewModel
    {
        $service = $landingPage->service;
        $city = $landingPage->city;

        $benefits = $this->composer->composeList($service->benefits, $city);
        $faq = $this->composer->composeFaq($service->faq, $city);

        $breadcrumbs = BreadcrumbBuilder::start()
            ->add($service->title, route('services.show', $service))
            ->add($city->name)
            ->build();

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->service($landingPage),
            $this->structuredData->faqPage($faq),
            $this->structuredData->breadcrumbList($breadcrumbs),
        );

        return new LandingPageViewModel(
            h1: $this->composer->compose($landingPage->custom_h1 ?? "{$service->title} em {$city->name}", $city),
            subtitle: $this->composer->compose($landingPage->custom_subtitle ?? $service->subtitle, $city),
            intro: $this->composer->compose($landingPage->custom_intro ?? $service->description, $city),
            benefits: $benefits,
            faq: $faq,
            ctaLabel: $this->composer->compose($landingPage->custom_cta ?? 'Solicitar orçamento', $city),
            seo: $this->seoMetaBuilder->forLandingPage($landingPage),
            breadcrumbs: $breadcrumbs,
            relatedLinks: $this->internalLinks->relatedLinks($landingPage),
            jsonLd: $jsonLd,
        );
    }
}
