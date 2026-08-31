<?php

namespace App\Services\Landing;

use App\Models\City;
use App\Models\State;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\StateViewModel;

final readonly class StateViewModelFactory
{
    public function __construct(
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
    ) {}

    public function makeForState(State $state): StateViewModel
    {
        $breadcrumbs = BreadcrumbBuilder::start()
            ->add('Estados', route('states.index'))
            ->add($state->name)
            ->build();

        $cities = $state->cities()->active()->orderByDesc('population')->get()
            ->map(fn (City $city): array => [
                'name' => $city->name,
                'uf' => $state->uf,
                'intro' => $city->intro,
                'url' => route('cities.show', $city),
            ])
            ->values()
            ->all();

        $jsonLd = [$this->structuredData->breadcrumbList($breadcrumbs)];

        return new StateViewModel(
            name: $state->name,
            uf: $state->uf,
            intro: $state->intro,
            businessText: $state->business_text,
            cities: $cities,
            seo: $this->seoMetaBuilder->forState($state),
            breadcrumbs: $breadcrumbs,
            jsonLd: $jsonLd,
        );
    }
}
