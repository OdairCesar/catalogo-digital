@use('App\Services\Seo\StructuredDataService')

@php
    $faqItems = [
        [
            'question' => 'Como o diagnóstico de viabilidade funciona?',
            'answer' => 'Você conversa com um consultor de IA sobre a sua ideia: o que ela é, para quem é, qual problema resolve, quais concorrentes você conhece e qual orçamento tem disponível. Com isso, a IA produz um diagnóstico com veredito de viabilidade, pontos fortes, riscos e próximos passos.',
        ],
        [
            'question' => 'O diagnóstico é definitivo?',
            'answer' => 'Não. É uma primeira avaliação honesta, incluindo riscos reais, não só pontos positivos. A decisão final é sempre sua.',
        ],
        [
            'question' => 'Preciso ter um plano de negócio pronto?',
            'answer' => 'Não. Descreva a ideia com suas palavras, mesmo que ainda esteja em formação.',
        ],
        [
            'question' => 'Quanto tempo leva?',
            'answer' => 'A conversa leva de 2 a 3 minutos, e o diagnóstico é gerado em menos de um minuto depois disso.',
        ],
        [
            'question' => 'Preciso pagar alguma coisa?',
            'answer' => 'Não, é 100% gratuito. Só pedimos nome, e-mail e telefone para te enviar o resultado.',
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

    $vitals = [
        ['label' => 'Descrição da ideia', 'reading' => 'O que ela é'],
        ['label' => 'Público-alvo', 'reading' => 'Para quem é'],
        ['label' => 'Problema resolvido', 'reading' => 'Que dor ela cura'],
        ['label' => 'Concorrentes', 'reading' => 'O que já existe parecido'],
        ['label' => 'Orçamento disponível', 'reading' => 'Quanto você pode investir'],
    ];

    $howItWorks = [
        'Descreva sua ideia pro consultor de IA, com suas palavras.',
        'Responda às perguntas de acompanhamento, no seu ritmo.',
        'Receba o veredito: pontos fortes, riscos e próximos passos.',
    ];
@endphp

{{--
    ESTRUTURA DESTA PÁGINA (diferente das outras 3): em vez de rolar por
    seções, quase tudo vive num único painel com ABAS clicáveis
    (Sinais vitais / Como funciona / Perguntas) — uma interação, não uma rolagem.
--}}
<x-layout.tool :title="$tool->title.' — OD Tec'" :description="$tool->description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <style>
        .tvi-ekg-path {
            stroke-dasharray: 400;
            stroke-dashoffset: 400;
            animation: tvi-ekg-draw 3.2s linear infinite;
        }
        @keyframes tvi-ekg-draw {
            0% { stroke-dashoffset: 400; }
            70% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: -20; }
        }
        [x-cloak] {
            display: none !important;
        }
        @media (prefers-reduced-motion: reduce) {
            .tvi-ekg-path {
                animation: none;
                stroke-dashoffset: 0;
            }
        }
    </style>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        {{-- HERO — monitor de sinais vitais, sem CTA aqui: o convite pra agir vive no painel de abas logo abaixo. --}}
        <section class="relative overflow-hidden bg-slate-900 px-5 py-16 text-white sm:px-8 sm:py-20 min-[960px]:px-14">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center text-center">
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/[0.12] px-4 py-[7px] font-mono text-[12px] font-bold tracking-[0.14em] text-emerald-300 uppercase">
                    Diagnóstico gratuito · IA
                </div>

                <div class="relative mb-8 w-full max-w-md overflow-hidden rounded-2xl bg-slate-950 px-6 py-6 shadow-[0_0_0_1px_rgba(255,255,255,0.06),0_20px_60px_-20px_rgba(0,0,0,0.6)]" aria-hidden="true">
                    <p class="mb-3 text-left font-mono text-[11px] tracking-[0.2em] text-emerald-500/70 uppercase">Analisando sinais...</p>
                    <svg viewBox="0 0 400 80" class="h-16 w-full" preserveAspectRatio="none">
                        <polyline
                            class="tvi-ekg-path"
                            points="0,40 60,40 80,10 100,70 120,40 180,40 200,20 220,60 240,40 400,40"
                            fill="none"
                            stroke="#34d399"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <h1 class="mb-5 max-w-2xl text-[32px] leading-[1.12] font-extrabold tracking-tight text-balance sm:text-[46px]">
                    Sua ideia tem pulso? Vamos verificar.
                </h1>

                <p class="max-w-xl text-lg leading-relaxed text-white/70">
                    Um diagnóstico honesto de viabilidade — pontos fortes, riscos reais e próximos passos. O exame começa no painel logo abaixo.
                </p>
            </div>
        </section>

        {{-- PAINEL DE EXAME — um único cartão com abas, em vez de seções empilhadas. --}}
        <section class="bg-slate-50 px-5 py-14 sm:px-8 lg:px-10">
            <div x-data="{ tab: 'sinais' }" class="mx-auto max-w-2xl overflow-hidden rounded-[28px] border border-slate-800/10 bg-white shadow-[0_30px_60px_-30px_rgba(15,23,42,0.25)]">
                <div class="flex border-b border-slate-800/10">
                    <button type="button" @click="tab = 'sinais'" :class="tab === 'sinais' ? 'text-emerald-600 border-emerald-500' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-center text-[13px] font-bold tracking-wide uppercase transition-colors">Sinais vitais</button>
                    <button type="button" @click="tab = 'como'" :class="tab === 'como' ? 'text-emerald-600 border-emerald-500' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-center text-[13px] font-bold tracking-wide uppercase transition-colors">Como funciona</button>
                    <button type="button" @click="tab = 'faq'" :class="tab === 'faq' ? 'text-emerald-600 border-emerald-500' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-center text-[13px] font-bold tracking-wide uppercase transition-colors">Perguntas</button>
                </div>

                <div class="p-6 sm:p-8">
                    <div x-show="tab === 'sinais'">
                        <p class="mb-5 text-[15px] leading-relaxed text-slate-500">Cinco perguntas que revelam a saúde real da sua ideia.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($vitals as $vital)
                                <div class="rounded-xl border border-slate-800/10 bg-slate-50 p-4">
                                    <p class="font-mono text-[10.5px] tracking-[0.14em] text-emerald-600 uppercase">{{ $vital['reading'] }}</p>
                                    <p class="mt-0.5 text-[14px] font-bold text-slate-800">{{ $vital['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tab === 'como'" x-cloak>
                        <p class="mb-5 text-[15px] leading-relaxed text-slate-500">Três passos, sem burocracia.</p>
                        <div class="space-y-4">
                            @foreach ($howItWorks as $index => $step)
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 font-mono text-[12px] font-bold text-emerald-400">{{ $index + 1 }}</span>
                                    <p class="pt-0.5 text-[14.5px] leading-relaxed text-slate-600">{{ $step }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tab === 'faq'" x-cloak>
                        <div class="divide-y divide-slate-100">
                            @foreach ($faqItems as $item)
                                <details class="group py-3" data-ga-event="faq_open" data-ga-payload="{{ json_encode(['question' => $item['question']]) }}">
                                    <summary class="flex cursor-pointer list-none items-start gap-3 font-semibold text-slate-800 marker:content-none">
                                        <span class="flex-1 text-[14.5px]">{{ $item['question'] }}</span>
                                        <svg class="mt-0.5 h-4 w-4 flex-none text-slate-400 transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                    </summary>
                                    <p class="mt-2 text-[13.5px] leading-relaxed text-slate-500">{{ $item['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 border-t border-white/10 bg-slate-900 p-5">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="mr-auto font-mono text-[11px] tracking-[0.14em] text-emerald-400 uppercase">Consultor de IA online</span>
                    <button
                        type="button"
                        @click="chatOpen = true"
                        data-ga-event="cta_click"
                        data-ga-payload="{{ json_encode(['location' => 'tool_panel', 'label' => 'Diagnosticar minha ideia']) }}"
                        class="inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-6 py-2.5 text-sm font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right"
                    >
                        Diagnosticar minha ideia
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
                data-ga-payload="{{ json_encode(['location' => 'tool_floating', 'label' => 'Diagnosticar agora']) }}"
                class="inline-flex items-center gap-2.5 rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_28px_-8px_rgba(37,99,235,0.55)] transition-[background-position,transform] duration-300 hover:-translate-y-0.5 hover:bg-right"
            >
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                </span>
                Diagnosticar agora
            </button>
        </div>

        {{-- MODAL --}}
        <div
            x-show="chatOpen"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-3 backdrop-blur-sm sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-label="Diagnóstico: minha ideia é viável"
        >
            <div
                @click.outside="chatOpen = false"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative flex h-full max-h-[720px] w-full max-w-lg flex-col rounded-[28px] bg-slate-900 p-3 shadow-[0_40px_80px_-20px_rgba(0,0,0,0.6)]"
            >
                <div class="mb-2 flex items-center justify-between px-3 pt-1">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-white/20"></span>
                        <span class="h-2 w-2 rounded-full bg-white/20"></span>
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                    </div>
                    <button
                        type="button"
                        @click="chatOpen = false"
                        aria-label="Fechar diagnóstico"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-white/50 transition-colors hover:bg-white/10 hover:text-white"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 rounded-3xl bg-white p-5 text-left sm:p-6">
                    <livewire:tool-chat :slug="$tool->slug" :key="$tool->slug" />
                </div>
            </div>
        </div>
    </div>
</x-layout.tool>
