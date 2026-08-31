@use('App\Services\Seo\StructuredDataService')

@php
    $faqItems = [
        [
            'question' => 'Como o desperdício é calculado?',
            'answer' => 'Você conta pra um consultor de IA quantas pessoas estão envolvidas no processo manual, quantas horas por semana ele consome, o custo aproximado da hora dessas pessoas e a frequência de erros ou retrabalho. Com isso, a IA estima o desperdício mensal e anual, e o quanto automação poderia economizar.',
        ],
        [
            'question' => 'Esse valor é exato?',
            'answer' => 'Não. É uma estimativa para dimensionar o problema. O objetivo é te ajudar a decidir se vale a pena investir em automação — não fechar uma conta exata.',
        ],
        [
            'question' => 'Preciso ter números exatos pra usar?',
            'answer' => 'Não. Uma estimativa aproximada de horas e custo já é suficiente.',
        ],
        [
            'question' => 'A ferramenta é paga?',
            'answer' => 'Não, é 100% gratuita e sem compromisso.',
        ],
        [
            'question' => 'E se o processo envolver mais de uma pessoa ou setor?',
            'answer' => 'Sem problema — descreva o processo como um todo. O consultor de IA pergunta os detalhes necessários pra estimar o impacto completo.',
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

    $factors = [
        ['label' => 'Pessoas envolvidas', 'detail' => 'Quantas pessoas fazem esse processo hoje'],
        ['label' => 'Horas por semana', 'detail' => 'Tempo gasto no processo manual, somado'],
        ['label' => 'Custo da hora', 'detail' => 'Custo aproximado da hora de trabalho dessas pessoas'],
        ['label' => 'Frequência de erros', 'detail' => 'Com que frequência o processo gera retrabalho'],
    ];
@endphp

{{--
    ESTRUTURA DESTA PÁGINA (diferente das outras): duas colunas lado a lado
    a partir de "lg", com o medidor fixo (sticky) à esquerda enquanto o
    conteúdo de apoio rola à direita — não é uma pilha de seções cheias.
--}}
<x-layout.tool :title="$tool->title.' — OD Tec'" :description="$tool->description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <style>
        @keyframes tvi-drip {
            0% { transform: translateY(0); opacity: 0.9; }
            85% { opacity: 0.4; }
            100% { transform: translateY(140px); opacity: 0; }
        }
        .tvi-drop {
            animation: tvi-drip 2.4s cubic-bezier(0.55,0,1,0.45) infinite;
        }
        @keyframes tvi-gauge-drain {
            0% { height: 92%; }
            100% { height: 18%; }
        }
        .tvi-gauge-fill {
            animation: tvi-gauge-drain 5s ease-in-out infinite alternate;
        }
        @media (prefers-reduced-motion: reduce) {
            .tvi-drop, .tvi-gauge-fill {
                animation: none;
            }
        }
    </style>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        <div class="lg:flex">
            {{-- COLUNA ESQUERDA — fixa na tela (lg+), com o "tanque" vazando e o CTA principal. --}}
            <div class="flex flex-col justify-center bg-slate-900 px-5 py-16 text-center text-white sm:px-8 lg:sticky lg:top-0 lg:h-screen lg:w-[42%] lg:px-12 lg:text-left">
                <div class="mx-auto max-w-sm lg:mx-0">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/[0.12] px-4 py-[7px] font-mono text-[12px] font-bold tracking-[0.14em] text-emerald-300 uppercase">
                        Diagnóstico gratuito · IA
                    </div>

                    <div class="relative mb-8 h-36 w-20 lg:mx-0" aria-hidden="true">
                        <div class="absolute inset-0 overflow-hidden rounded-b-xl rounded-t-md border-2 border-white/25 bg-slate-950">
                            <div class="tvi-gauge-fill absolute bottom-0 w-full bg-gradient-to-t from-emerald-600 to-emerald-400"></div>
                        </div>
                        <span class="tvi-drop absolute -bottom-2 left-1/2 h-3 w-2.5 -translate-x-1/2 rounded-b-full rounded-t-sm bg-emerald-400"></span>
                    </div>

                    <h1 class="mb-5 text-[30px] leading-[1.12] font-extrabold tracking-tight text-balance sm:text-[40px]">
                        Sua empresa está vazando dinheiro. Quanto por mês?
                    </h1>

                    <p class="mb-8 text-[17px] leading-relaxed text-white/70">
                        Conte pra um consultor de IA como funciona um processo manual da sua empresa e descubra o custo real disso.
                    </p>

                    <button
                        type="button"
                        @click="chatOpen = true"
                        data-ga-event="cta_click"
                        data-ga-payload="{{ json_encode(['location' => 'tool_hero', 'label' => 'Calcular o desperdício']) }}"
                        class="inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-8 py-3.5 text-sm font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right"
                    >
                        Calcular meu desperdício
                    </button>

                    <p class="mt-4 font-mono text-[12px] tracking-wide text-white/45 uppercase">Grátis · Sem cartão · Resultado na hora</p>
                </div>
            </div>

            {{-- COLUNA DIREITA — rola normalmente: fatores, chamada pro chat e FAQ. --}}
            <div class="bg-slate-50 px-5 py-16 sm:px-8 lg:w-[58%] lg:px-14 lg:py-20">
                <div class="mx-auto max-w-xl">
                    <p class="mb-3 font-mono text-[11px] font-bold tracking-[0.16em] text-emerald-600 uppercase">O que a IA considera</p>
                    <div class="mb-10 grid gap-3 sm:grid-cols-2">
                        @foreach ($factors as $index => $factor)
                            <div
                                data-reveal
                                style="transition-delay: {{ $index * 60 }}ms"
                                class="rounded-2xl border border-slate-800/10 bg-white p-5"
                            >
                                <p class="font-bold text-slate-800">{{ $factor['label'] }}</p>
                                <p class="mt-1 text-[13.5px] leading-relaxed text-slate-500">{{ $factor['detail'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        @click="chatOpen = true"
                        data-ga-event="cta_click"
                        data-ga-payload="{{ json_encode(['location' => 'tool_teaser', 'label' => 'Abrir diagnóstico']) }}"
                        class="group mb-10 block w-full rounded-2xl bg-slate-900 p-6 text-left transition-transform duration-150 hover:-translate-y-0.5"
                    >
                        <p class="mb-4 flex items-center gap-2 font-mono text-[11px] font-bold tracking-[0.16em] text-emerald-400 uppercase">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            Medidor de desperdício · IA online
                        </p>

                        <div class="mx-auto mb-4 h-20 w-40" aria-hidden="true">
                            <svg viewBox="0 0 200 110" class="h-full w-full">
                                <path d="M10,100 A90,90 0 0,1 190,100" fill="none" stroke="#1e293b" stroke-width="14" stroke-linecap="round" />
                                <path d="M10,100 A90,90 0 0,1 145,28" fill="none" stroke="#34d399" stroke-width="14" stroke-linecap="round" />
                                <line x1="100" y1="100" x2="152" y2="42" stroke="#f8fafc" stroke-width="4" stroke-linecap="round" />
                                <circle cx="100" cy="100" r="7" fill="#f8fafc" />
                            </svg>
                        </div>

                        <p class="mb-4 text-center text-[14.5px] leading-relaxed text-white/70">"Quantas pessoas fazem esse processo hoje?"</p>

                        <div class="flex items-center justify-center gap-2 border-t border-white/10 pt-4 font-mono text-[12px] font-bold tracking-[0.14em] text-emerald-400 uppercase transition-colors group-hover:text-emerald-300">
                            Toque para abrir o diagnóstico
                            <span class="transition-transform group-hover:translate-x-1">→</span>
                        </div>
                    </button>

                    <p class="mb-3 font-mono text-[11px] font-bold tracking-[0.16em] text-emerald-600 uppercase">Perguntas frequentes</p>
                    <div class="mb-10 flex flex-col gap-3">
                        @foreach ($faqItems as $item)
                            <details class="group rounded-xl border border-slate-800/10 bg-white open:shadow-sm" data-ga-event="faq_open" data-ga-payload="{{ json_encode(['question' => $item['question']]) }}">
                                <summary class="flex cursor-pointer list-none items-start gap-3 px-5 py-4 font-semibold text-slate-800 marker:content-none">
                                    <span class="mt-0.5 shrink-0 font-mono text-[11px] font-bold text-emerald-600">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="flex-1 text-[14.5px]">{{ $item['question'] }}</span>
                                    <svg class="mt-0.5 h-4 w-4 flex-none text-slate-400 transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                </summary>
                                <p class="px-5 pb-4 pl-[3rem] text-[13.5px] leading-relaxed text-slate-500">{{ $item['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>

                    <div class="text-center">
                        <button
                            type="button"
                            @click="chatOpen = true"
                            data-ga-event="cta_click"
                            data-ga-payload="{{ json_encode(['location' => 'tool_footer', 'label' => 'Calcular meu desperdício']) }}"
                            class="inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-8 py-3.5 text-sm font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right"
                        >
                            Calcular meu desperdício
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA FLUTUANTE — só no mobile, já que no desktop a coluna esquerda fica fixa com o CTA sempre visível. --}}
        <div
            x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => { show = window.scrollY > 700 })"
            x-show="show"
            x-transition
            style="display: none;"
            class="fixed right-5 bottom-5 z-40 lg:hidden"
        >
            <button
                type="button"
                @click="chatOpen = true"
                data-ga-event="cta_click"
                data-ga-payload="{{ json_encode(['location' => 'tool_floating', 'label' => 'Calcular agora']) }}"
                class="inline-flex items-center gap-2.5 rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_28px_-8px_rgba(37,99,235,0.55)] transition-[background-position,transform] duration-300 hover:-translate-y-0.5 hover:bg-right"
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-3 backdrop-blur-sm sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-label="Calculadora: quanto sua empresa desperdiça com processos manuais"
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
