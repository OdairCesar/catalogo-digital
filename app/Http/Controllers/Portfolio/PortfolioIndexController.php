<?php

namespace App\Http\Controllers\Portfolio;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Service;
use App\Services\Portfolio\PortfolioItemViewModelFactory;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\StructuredDataService;
use Illuminate\Contracts\View\View;

class PortfolioIndexController extends Controller
{
    public function __construct(
        private readonly PortfolioItemViewModelFactory $viewModelFactory,
        private readonly StructuredDataService $structuredData,
    ) {}

    public function index(?Service $service = null): View
    {
        $items = ($service?->portfolioItems() ?? Section::query()->ofType(SectionType::Portfolio))
            ->active()
            ->whereNotNull('slug')
            ->with('service')
            ->latest()
            ->paginate(12)
            ->through(fn (Section $item): array => $this->viewModelFactory->teaser($item));

        $services = Service::query()
            ->whereIn('id', Section::query()->ofType(SectionType::Portfolio)->active()->select('service_id'))
            ->orderBy('name')
            ->get();

        $breadcrumbs = BreadcrumbBuilder::start()->add('Portfólio', $service ? route('portfolio.index') : null);

        if ($service) {
            $breadcrumbs->add($service->title);
        }

        $breadcrumbs = $breadcrumbs->build();

        return view('pages.portfolio.index', [
            'items' => $items,
            'services' => $services,
            'service' => $service,
            'breadcrumbs' => $breadcrumbs,
            'jsonLd' => [$this->structuredData->breadcrumbList($breadcrumbs)],
        ]);
    }
}
