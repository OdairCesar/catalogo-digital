<?php

namespace App\Http\Controllers;

use App\Enums\SectionType;
use App\Models\Company;
use App\Models\Section;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\StructuredDataService;
use Illuminate\Contracts\View\View;

class FaqIndexController extends Controller
{
    public function __construct(
        private readonly StructuredDataService $structuredData,
    ) {}

    public function index(): View
    {
        $groups = $this->groups();

        $breadcrumbs = BreadcrumbBuilder::start()->add('Perguntas frequentes')->build();

        $allFaq = collect($groups)->flatMap(fn (array $group): array => $group['faq'])->all();

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->faqPage($allFaq),
            $this->structuredData->breadcrumbList($breadcrumbs),
        );

        return view('pages.faq.index', [
            'groups' => $groups,
            'breadcrumbs' => $breadcrumbs,
            'jsonLd' => $jsonLd,
            'whatsappLink' => Company::current()?->whatsappLink('Oi Cae! Fiquei com uma dúvida 💜'),
        ]);
    }

    /**
     * @return list<array{title: ?string, faq: list<array{question: string, answer: string}>, extra_fields: array<int, array{label: string, value: string}>}>
     */
    private function groups(): array
    {
        return Section::query()->ofType(SectionType::FaqGroup)->active()->ordered()->get()
            ->map(fn (Section $group): array => [
                'title' => $group->title,
                'faq' => $group->data['faq'] ?? [],
                'extra_fields' => $group->extra_fields ?? [],
            ])
            ->all();
    }
}
