<?php

namespace App\Services\Landing;

use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\ContentComposer;
use App\Services\Seo\InternalLinkService;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\CityViewModel;
use App\ViewModels\ServiceViewModel;
use Illuminate\Support\Facades\Storage;

final readonly class ServiceCityViewModelFactory
{
    public function __construct(
        private ContentComposer $composer,
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
        private InternalLinkService $internalLinks,
    ) {}

    public function makeForService(Service $service): ServiceViewModel
    {
        $breadcrumbs = BreadcrumbBuilder::start()
            ->add('Serviços', route('services.index'))
            ->add($service->title)
            ->build();

        $subtitle = $this->composer->compose($service->subtitle);
        $faq = $this->composer->composeFaq($service->faq);

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->serviceGeneric($service, $subtitle),
            $this->structuredData->faqPage($faq),
            $this->structuredData->breadcrumbList($breadcrumbs),
        );

        return new ServiceViewModel(
            title: $service->title,
            subtitle: $subtitle,
            description: $this->composer->compose($service->description),
            benefits: $this->composer->composeList($service->benefits),
            faq: $faq,
            seo: $this->seoMetaBuilder->forService($service),
            heroImageUrl: $service->hero_image ? Storage::disk('cloudinary')->url($service->hero_image) : null,
            breadcrumbs: $breadcrumbs,
            relatedLinks: $this->internalLinks->clustersForService($service),
            jsonLd: $jsonLd,
        );
    }

    public function makeForCity(City $city): CityViewModel
    {
        $breadcrumbs = BreadcrumbBuilder::start()
            ->add('Cidades', route('cities.index'))
            ->add($city->name)
            ->build();

        $landingPages = $city->landingPages()->published()->with('service')->get()
            ->map(fn (LandingPage $landingPage): array => [
                'label' => $landingPage->service->title,
                'subtitle' => $this->composer->compose($landingPage->service->subtitle, $city),
                'icon' => $landingPage->service->icon,
                'url' => route('landing.show', $landingPage),
            ])
            ->values()
            ->all();

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->localBusiness($city),
            $this->structuredData->breadcrumbList($breadcrumbs),
        );

        $state = $city->state;

        return new CityViewModel(
            name: $city->name,
            uf: $state !== null ? $state->uf : '',
            region: $city->region,
            intro: $city->intro,
            businessText: $city->business_text,
            landingPages: $landingPages,
            seo: $this->seoMetaBuilder->forCity($city),
            breadcrumbs: $breadcrumbs,
            jsonLd: $jsonLd,
        );
    }
}
