<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Seo\ContentComposer;
use App\Services\Seo\StructuredDataService;
use Illuminate\Contracts\View\View;

class ServiceIndexController extends Controller
{
    public function __construct(
        private readonly ContentComposer $composer,
        private readonly StructuredDataService $structuredData,
    ) {}

    public function index(): View
    {
        $services = Service::query()->active()->orderBy('name')->get()
            ->map(fn (Service $service): array => [
                'title' => $service->title,
                'subtitle' => $this->composer->compose($service->subtitle),
                'icon' => $service->icon,
                'url' => route('services.show', $service),
            ]);

        $breadcrumbs = BreadcrumbBuilder::start()->add('Serviços')->build();

        $faq = [
            ['question' => 'Quanto tempo leva para desenvolver um projeto?', 'answer' => 'Depende do escopo, mas a maioria dos MVPs e sites institucionais fica pronta em poucas semanas. Definimos um prazo claro já no orçamento.'],
            ['question' => 'Como funciona o orçamento?', 'answer' => 'Entendemos sua necessidade, propomos uma solução e enviamos um orçamento fechado, sem taxas escondidas.'],
            ['question' => 'Vocês dão suporte após o lançamento?', 'answer' => 'Sim. Acompanhamos o projeto após o lançamento e oferecemos planos de manutenção e evolução contínua.'],
            ['question' => 'Preciso ter conhecimento técnico para acompanhar o projeto?', 'answer' => 'Não. Explicamos cada etapa em linguagem simples e você acompanha o progresso sem precisar entender de código.'],
        ];

        $jsonLd = $this->structuredData->combine(
            $this->structuredData->breadcrumbList($breadcrumbs),
            $this->structuredData->faqPage($faq),
        );

        return view('pages.services.index', [
            'services' => $services,
            'breadcrumbs' => $breadcrumbs,
            'faq' => $faq,
            'jsonLd' => $jsonLd,
        ]);
    }
}
