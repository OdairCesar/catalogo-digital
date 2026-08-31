<?php

namespace App\Services\Seo;

use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use Closure;
use Illuminate\Support\Collection;

final class InternalLinkService
{
    public function __construct(
        private readonly ContentComposer $composer,
        private readonly int $limit = 6,
    ) {}

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function otherCitiesForService(LandingPage $landingPage): array
    {
        $city = $landingPage->city;

        $candidates = $landingPage->service->landingPages()
            ->published()
            ->where('city_id', '!=', $city->id)
            ->with('city')
            ->get();

        return $this->sortedCityLinks(
            $candidates,
            $city,
            cityResolver: fn (LandingPage $candidate): City => $candidate->city,
            urlResolver: fn (LandingPage $candidate): string => route('landing.show', $candidate),
        );
    }

    /**
     * Ranks candidates by same-region-first, then maps each to a {label, url} link.
     *
     * @template TCandidate of LandingPage|ServiceClusterLandingPage
     *
     * @param  Collection<int, TCandidate>  $candidates
     * @param  Closure(TCandidate): City  $cityResolver
     * @param  Closure(TCandidate): string  $urlResolver
     * @return array<int, array{label: string, url: string}>
     */
    private function sortedCityLinks(Collection $candidates, City $currentCity, Closure $cityResolver, Closure $urlResolver): array
    {
        $sameRegionCityIds = City::query()->sameRegionAs($currentCity)->pluck('id');

        return $candidates
            ->sortByDesc(fn ($candidate): bool => $sameRegionCityIds->contains($cityResolver($candidate)->id))
            ->take($this->limit)
            ->map(fn ($candidate): array => [
                'label' => $cityResolver($candidate)->name,
                'url' => $urlResolver($candidate),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function otherServicesForCity(LandingPage $landingPage): array
    {
        $candidates = $landingPage->city->landingPages()
            ->published()
            ->where('service_id', '!=', $landingPage->service_id)
            ->with('service')
            ->take($this->limit)
            ->get();

        return $candidates
            ->map(fn (LandingPage $candidate): array => [
                'label' => $candidate->service->title,
                'url' => route('landing.show', $candidate),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function relatedLinks(LandingPage $landingPage): array
    {
        return [
            ...$this->otherCitiesForService($landingPage),
            ...$this->otherServicesForCity($landingPage),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function clustersForService(Service $service): array
    {
        return $service->clusters()
            ->published()
            ->take($this->limit)
            ->get()
            ->map(fn (ServiceCluster $cluster): array => [
                'label' => $this->composer->compose((string) $cluster->title),
                'url' => route('services.clusters.show', [$service, $cluster]),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function otherClustersForService(ServiceCluster $cluster): array
    {
        $candidates = $cluster->service->clusters()
            ->published()
            ->where('id', '!=', $cluster->id)
            ->take($this->limit)
            ->get();

        return $candidates
            ->map(fn (ServiceCluster $candidate): array => [
                'label' => $this->composer->compose((string) $candidate->title),
                'url' => route('services.clusters.show', [$cluster->service, $candidate]),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function otherCitiesForCluster(ServiceClusterLandingPage $pivot): array
    {
        $cluster = $pivot->serviceCluster;
        $city = $pivot->city;

        $candidates = $cluster->landingPages()
            ->published()
            ->where('city_id', '!=', $city->id)
            ->with('city')
            ->get();

        return $this->sortedCityLinks(
            $candidates,
            $city,
            cityResolver: fn (ServiceClusterLandingPage $candidate): City => $candidate->city,
            urlResolver: fn (ServiceClusterLandingPage $candidate): string => route('services.clusters.show', [$cluster->service, $candidate->slug]),
        );
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function relatedLinksForCluster(ServiceClusterLandingPage $pivot): array
    {
        return [
            ...$this->otherCitiesForCluster($pivot),
            ...$this->otherClustersForService($pivot->serviceCluster),
        ];
    }
}
