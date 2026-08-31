<?php

namespace App\Services\Portfolio;

use App\Enums\SectionType;
use App\Models\Section;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\SeoMetaBuilder;
use App\Services\Seo\StructuredDataService;
use App\ViewModels\PortfolioItemViewModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final readonly class PortfolioItemViewModelFactory
{
    public function __construct(
        private SeoMetaBuilder $seoMetaBuilder,
        private StructuredDataService $structuredData,
    ) {}

    public function makeShow(Section $portfolioItem): PortfolioItemViewModel
    {
        $breadcrumbs = BreadcrumbBuilder::start()
            ->add('Portfólio', route('portfolio.index'))
            ->add($portfolioItem->title)
            ->build();

        $relatedItems = Section::query()
            ->ofType(SectionType::Portfolio)
            ->active()
            ->whereNotNull('slug')
            ->with('service')
            ->when($portfolioItem->service_id, fn (Builder $query): Builder => $query->where('service_id', $portfolioItem->service_id))
            ->whereKeyNot($portfolioItem->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Section $related): array => $this->teaser($related))
            ->all();

        $jsonLd = [$this->structuredData->breadcrumbList($breadcrumbs)];

        return new PortfolioItemViewModel(
            title: $portfolioItem->title ?? '',
            excerpt: $portfolioItem->excerpt ?? '',
            content: $portfolioItem->content,
            coverImageUrl: $this->coverImageUrl($portfolioItem),
            externalUrl: $portfolioItem->data['external_url'] ?? null,
            serviceName: $portfolioItem->service?->title,
            serviceUrl: $portfolioItem->service ? route('services.show', $portfolioItem->service) : null,
            relatedItems: $relatedItems,
            seo: $this->seoMetaBuilder->forPortfolioItem($portfolioItem),
            breadcrumbs: $breadcrumbs,
            jsonLd: $jsonLd,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function teaser(Section $portfolioItem): array
    {
        return [
            'title' => $portfolioItem->title,
            'excerpt' => $portfolioItem->excerpt,
            'url' => route('portfolio.show', $portfolioItem->slug),
            'coverImageUrl' => $this->coverImageUrl($portfolioItem),
            'serviceName' => $portfolioItem->service?->title,
        ];
    }

    private function coverImageUrl(Section $portfolioItem): ?string
    {
        return $portfolioItem->image ? Storage::disk('cloudinary')->url($portfolioItem->image) : null;
    }
}
