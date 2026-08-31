@use('App\Services\Seo\StructuredDataService')

@php
    $faqItems = [
        [
            'question' => 'Como o orçamento é calculado?',
            'answer' => 'Você conversa com um consultor de IA sobre o tipo de projeto, as funcionalidades principais, as integrações necessárias e a urgência do prazo. Com isso, a IA estima uma faixa de investimento, o prazo e a complexidade técnica do projeto.',
        ],
        [
            'question' => 'Esse orçamento é o valor final?',
            'answer' => 'Não. É uma estimativa inicial para você planejar antes de conversar com a equipe. O valor final depende de uma análise técnica detalhada.',
        ],
        [
            'question' => 'Preciso saber termos técnicos para responder?',
            'answer' => 'Não. O consultor de IA pergunta sobre o funcionamento do seu negócio, nunca sobre tecnologia.',
        ],
        [
            'question' => 'Quanto tempo leva?',
            'answer' => 'A conversa leva de 2 a 3 minutos. O orçamento é calculado na sequência, em menos de um minuto.',
        ],
        [
            'question' => 'Vocês constroem o projeto depois?',
            'answer' => 'Se o orçamento fizer sentido pra você, sim — é só falar com a equipe ao final do resultado. Usar a calculadora não gera compromisso.',
        ],
    ];

    $breadcrumbs = [
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Ferramentas', 'url' => route('tools.index')],
        ['label' => $tool->title],
    ];

    $structuredData = app(StructuredDataService::class);

    $jsonLd = array_values(array_filter([
        $structuredData->breadcrumbList($breadcrumbs),
        $structuredData->faqPage($faqItems),
    ]));

    $specs = [
        ['code' => 'A', 'label' => 'Tipo de projeto', 'detail' => 'Site, sistema web, aplicativo ou automação'],
        ['code' => 'B', 'label' => 'Funcionalidades', 'detail' => 'O que o sistema precisa fazer no dia a dia'],
        ['code' => 'C', 'label' => 'Integrações', 'detail' => 'Pagamento, WhatsApp, ERPs e outros sistemas'],
        ['code' => 'D', 'label' => 'Urgência', 'detail' => 'Prazo desejado para o lançamento'],
    ];

    $notes = [
        ['n' => '01', 'title' => 'Levantamento', 'text' => 'O consultor de IA entende o escopo do seu projeto.'],
        ['n' => '02', 'title' => 'Cálculo', 'text' => 'Estimamos investimento, prazo e complexidade técnica.'],
        ['n' => '03', 'title' => 'Entrega', 'text' => 'Você recebe o orçamento na tela, com opção de PDF.'],
    ];
@endphp

{{--
    ESTRUTURA DESTA PÁGINA (deliberadamente diferente das outras 3):
    em vez de várias seções empilhadas, quase tudo vive dentro de UMA
    única "folha de projeto" — como uma prancha de desenho técnico real,
    com zonas internas separadas por réguas, não cartões soltos.
--}}
<x-layout.tool :title="$tool->title.' — OD Tec'" :description="$tool->description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <style>
        .tvi-blueprint-grid {
            background-image:
                repeating-linear-gradient(0deg, rgba(255,255,255,0.07) 0px, rgba(255,255,255,0.07) 1px, transparent 1px, transparent 32px),
                repeating-linear-gradient(90deg, rgba(255,255,255,0.07) 0px, rgba(255,255,255,0.07) 1px, transparent 1px, transparent 32px);
        }
        @keyframes tvi-blueprint-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0.35); }
            50% { box-shadow: 0 0 0 16px rgba(37,99,235,0); }
        }
        .tvi-blueprint-frame {
            animation: tvi-blueprint-glow 2.6s ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .tvi-blueprint-frame {
                animation: none;
            }
        }
    </style>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        {{-- HERO — planta técnica: grid de blueprint, carimbo de projeto no canto. --}}
        <section class="tvi-blueprint-grid relative overflow-hidden bg-blue-950 px-5 py-20 text-white sm:px-8 sm:py-24 min-[960px]:px-14">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center text-center">
                <div class="mb-8 inline-flex items-center gap-2 rounded-sm border border-white/25 px-4 py-[7px] font-mono text-[12px] font-bold tracking-[0.14em] text-blue-200 uppercase">
                    Orçamento gratuito · IA
                </div>

                <h1 class="mb-5 max-w-2xl text-[32px] leading-[1.12] font-extrabold tracking-tight text-balance sm:text-[46px]">
                    Todo sistema começa com uma planta. E um orçamento.
                </h1>

                <p class="mb-2 max-w-xl text-lg leading-relaxed text-white/70">
                    Conte o que você precisa construir para um consultor de IA e receba uma faixa de investimento, prazo estimado e complexidade técnica.
                </p>
                <p class="font-mono text-[12px] tracking-wide text-white/45 uppercase">A folha do projeto está logo abaixo ↓</p>
            </div>
        </section>

        {{-- A FOLHA DO PROJETO — uma única prancha contínua, dividida por réguas internas, em vez de seções soltas. --}}
        <section class="bg-slate-100 px-5 py-14 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-3xl border-2 border-blue-950 bg-white">
                {{-- carimbo --}}
                <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 border-b-2 border-blue-950 bg-blue-950 p-5 font-mono text-[11px] text-white/70 sm:grid-cols-4">
                    <span>PROJETO<br><span class="text-white">SISTEMA SOB MEDIDA</span></span>
                    <span>ESCALA<br><span class="text-white">1:1</span></span>
                    <span>REV.<br><span class="text-white">01</span></span>
                    <span>DESENHISTA<br><span class="text-white">IA · OD TEC</span></span>
                </div>

                {{-- legenda / especificações --}}
                <div class="border-b border-dashed border-blue-950/25 p-6 sm:p-8">
                    <p class="mb-4 font-mono text-[11px] font-bold tracking-[0.16em] text-blue-600 uppercase">Legenda · o que entra no orçamento</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($specs as $spec)
                            <div class="flex items-start gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-sm bg-blue-950 font-mono text-xs font-bold text-blue-300">{{ $spec['code'] }}</span>
                                <div>
                                    <p class="text-[14.5px] font-bold text-slate-800">{{ $spec['label'] }}</p>
                                    <p class="text-[13.5px] leading-relaxed text-slate-500">{{ $spec['detail'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- anotação / chamada para o chat --}}
                <div class="border-b border-dashed border-blue-950/25 p-6 sm:p-8">
                    <button
                        type="button"
                        @click="chatOpen = true"
                        data-ga-event="cta_click"
                        data-ga-payload="{{ json_encode(['location' => 'tool_teaser', 'label' => 'Abrir orçamento']) }}"
                        class="tvi-blueprint-frame group relative block w-full rounded-sm border border-blue-950/30 bg-blue-950/[0.03] p-6 text-left transition-colors hover:bg-blue-950/[0.06]"
                    >
                        <span class="absolute top-2 left-2 h-3 w-3 border-t-2 border-l-2 border-blue-950/50" aria-hidden="true"></span>
                        <span class="absolute top-2 right-2 h-3 w-3 border-t-2 border-r-2 border-blue-950/50" aria-hidden="true"></span>
                        <span class="absolute bottom-2 left-2 h-3 w-3 border-b-2 border-l-2 border-blue-950/50" aria-hidden="true"></span>
                        <span class="absolute right-2 bottom-2 h-3 w-3 border-r-2 border-b-2 border-blue-950/50" aria-hidden="true"></span>

                        <p class="mb-2 flex items-center gap-2 font-mono text-[11px] font-bold tracking-[0.16em] text-blue-600 uppercase">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-500 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-600"></span>
                            </span>
                            Anotação · consultor de IA online agora
                        </p>
                        <p class="mb-4 max-w-md font-mono text-[16px] leading-relaxed text-slate-800">"Olá! Para orçar seu projeto, qual é o seu nome?"</p>
                        <div class="flex items-center gap-2 font-mono text-[12px] font-bold tracking-[0.14em] text-blue-950 uppercase transition-colors group-hover:text-blue-600">
                            Toque para abrir o orçamento
                            <span class="transition-transform group-hover:translate-x-1">→</span>
                        </div>
                    </button>
                </div>

                {{-- notas de execução --}}
                <div class="border-b border-dashed border-blue-950/25 p-6 sm:p-8">
                    <p class="mb-4 font-mono text-[11px] font-bold tracking-[0.16em] text-blue-600 uppercase">Notas de execução</p>
                    <div class="space-y-4">
                        @foreach ($notes as $note)
                            <div class="flex items-start gap-3">
                                <span class="font-mono text-[12px] font-bold text-blue-600">{{ $note['n'] }}</span>
                                <p class="text-[14.5px] leading-relaxed text-slate-600"><strong class="text-slate-800">{{ $note['title'] }}:</strong> {{ $note['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- perguntas frequentes, em linha, sem cartões soltos --}}
                <div class="border-b border-dashed border-blue-950/25 p-6 sm:p-8">
                    <p class="mb-4 font-mono text-[11px] font-bold tracking-[0.16em] text-blue-600 uppercase">Perguntas frequentes</p>
                    <div class="divide-y divide-dashed divide-blue-950/15">
                        @foreach ($faqItems as $item)
                            <details class="group py-3" data-ga-event="faq_open" data-ga-payload="{{ json_encode(['question' => $item['question']]) }}">
                                <summary class="flex cursor-pointer list-none items-start gap-3 font-semibold text-slate-800 marker:content-none">
                                    <span class="mt-0.5 shrink-0 font-mono text-[11px] font-bold text-blue-600">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="flex-1 text-[14.5px]">{{ $item['question'] }}</span>
                                    <svg class="mt-0.5 h-4 w-4 flex-none text-slate-400 transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                </summary>
                                <p class="mt-2 pl-6 text-[13.5px] leading-relaxed text-slate-500">{{ $item['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>

                {{-- assinatura / cta final --}}
                <div class="flex flex-col items-center gap-3 p-6 text-center sm:p-8">
                    <p class="font-mono text-[11px] tracking-[0.16em] text-slate-400 uppercase">Aprovação</p>
                    <button
                        type="button"
                        @click="chatOpen = true"
                        data-ga-event="cta_click"
                        data-ga-payload="{{ json_encode(['location' => 'tool_signoff', 'label' => 'Calcular meu orçamento']) }}"
                        class="inline-block rounded-sm bg-blue-600 px-9 py-4 text-base font-bold text-white transition-transform duration-200 hover:-translate-y-0.5 hover:bg-blue-500"
                    >
                        Calcular meu orçamento
                    </button>
                </div>
            </div>
        </section>

        {{-- CTA FLUTUANTE --}}
        <div
            x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => { show = window.scrollY > 700 })"
            x-show="show"
            x-transition
            style="display: none;"
            class="fixed right-5 bottom-5 z-40 sm:right-8 sm:bottom-8"
        >
            <button
                type="button"
                @click="chatOpen = true"
                data-ga-event="cta_click"
                data-ga-payload="{{ json_encode(['location' => 'tool_floating', 'label' => 'Calcular agora']) }}"
                class="inline-flex items-center gap-2.5 rounded-sm bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_28px_-8px_rgba(37,99,235,0.55)] transition-transform duration-200 hover:-translate-y-0.5 hover:bg-blue-500"
            >
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                </span>
                Calcular agora
            </button>
        </div>

        {{-- MODAL --}}
        <div
            x-show="chatOpen"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-blue-950/85 p-3 backdrop-blur-sm sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-label="Calculadora: quanto custa desenvolver meu sistema"
        >
            <div
                @click.outside="chatOpen = false"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative flex h-full max-h-[720px] w-full max-w-lg flex-col rounded-sm bg-blue-950 p-3 shadow-[0_40px_80px_-20px_rgba(0,0,0,0.6)]"
            >
                <div class="mb-2 flex items-center justify-between px-3 pt-1">
                    <span class="font-mono text-[11px] tracking-[0.14em] text-blue-300 uppercase">Consultor de IA</span>
                    <button
                        type="button"
                        @click="chatOpen = false"
                        aria-label="Fechar orçamento"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-white/50 transition-colors hover:bg-white/10 hover:text-white"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 rounded-sm bg-white p-5 text-left sm:p-6">
                    <livewire:tool-chat :slug="$tool->slug" :key="$tool->slug" />
                </div>
            </div>
        </div>
    </div>
</x-layout.tool>
