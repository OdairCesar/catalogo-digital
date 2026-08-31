@use('App\Services\Seo\StructuredDataService')

@php
    $faqItems = [
        [
            'question' => 'Como a calculadora estima o valor da minha ideia?',
            'answer' => 'Você conversa por alguns minutos com um consultor de IA, que pergunta sobre o tipo de negócio, quantos clientes você espera atender, se vai cobrar assinatura ou venda avulsa, o ticket médio e a equipe necessária. Com essas respostas, a IA cruza padrões de negócios parecidos e calcula uma faixa de faturamento mensal, o custo estimado do MVP e um roteiro inicial.',
        ],
        [
            'question' => 'Esse número é uma garantia de faturamento?',
            'answer' => 'Não. É uma estimativa inicial para te ajudar a decidir se vale a pena investir tempo e dinheiro na ideia. O valor real depende de execução, mercado e concorrência — mas ter uma faixa realista já evita meses de trabalho em cima de um palpite.',
        ],
        [
            'question' => 'Preciso pagar alguma coisa para usar?',
            'answer' => 'Não. A calculadora é 100% gratuita, sem cartão de crédito e sem compromisso. Só pedimos nome, e-mail e telefone para te enviar o resultado.',
        ],
        [
            'question' => 'Quanto tempo leva para receber o resultado?',
            'answer' => 'A conversa com o consultor de IA leva de 2 a 3 minutos. Depois disso, o cálculo é gerado em menos de um minuto e o resultado aparece na mesma tela, com opção de baixar em PDF.',
        ],
        [
            'question' => 'E se minha ideia ainda estiver só na cabeça, sem nome nem plano?',
            'answer' => 'Sem problema. Quanto mais cedo você calcular, melhor — é exatamente para isso que a ferramenta existe. Descreva a ideia com suas palavras, mesmo que ainda esteja crua, que o consultor de IA ajuda a organizar o resto.',
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

    $keys = [
        ['glyph' => '?', 'label' => 'Tipo de negócio'],
        ['glyph' => '×', 'label' => 'Clientes esperados'],
        ['glyph' => '÷', 'label' => 'Modelo de cobrança'],
        ['glyph' => 'R$', 'label' => 'Ticket médio'],
        ['glyph' => '+', 'label' => 'Equipe necessária'],
    ];

    $tickerItems = ['Pet shop', 'SaaS B2B', 'Loja virtual', 'Consultoria', 'App de delivery', 'Clínica', 'Marketplace', 'Curso online', 'Assinatura mensal', 'Agência'];
@endphp

<x-layout.tool :title="$tool->title.' — OD Tec'" :description="$tool->description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <style>
        @keyframes tvi-marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        .tvi-marquee-track {
            animation: tvi-marquee 26s linear infinite;
        }
        .tvi-lcd::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(0deg, rgba(255,255,255,0.035) 0px, rgba(255,255,255,0.035) 1px, transparent 1px, transparent 3px);
            pointer-events: none;
            border-radius: inherit;
        }
        @keyframes tvi-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.32); }
            50% { box-shadow: 0 0 0 16px rgba(16,185,129,0); }
        }
        .tvi-frame {
            animation: tvi-glow 2.6s ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .tvi-marquee-track,
            .tvi-frame {
                animation: none;
            }
        }
    </style>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        {{-- HERO — o "visor" da calculadora é a tese da página: um número que nunca para de mudar até você calcular o seu. --}}
        <section class="relative overflow-hidden bg-slate-900 px-5 py-20 text-white sm:px-8 sm:py-28 min-[960px]:px-14">
            <div class="pointer-events-none absolute -top-24 -right-24 h-[420px] w-[420px] rounded-full bg-emerald-500/[0.12] blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-24 h-[380px] w-[380px] rounded-full bg-blue-600/[0.18] blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto flex max-w-3xl flex-col items-center text-center">
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/[0.12] px-4 py-[7px] font-mono text-[12px] font-bold tracking-[0.14em] text-emerald-300 uppercase">
                    Calculadora gratuita · IA
                </div>

                <div
                    x-data="{
                        display: 'R$ 8.200 /mês',
                        values: ['R$ 8.200 /mês', 'R$ 15.600 /mês', 'R$ 22.900 /mês', 'R$ 34.100 /mês', 'R$ 47.500 /mês', 'R$ 61.000 /mês'],
                        i: 0,
                    }"
                    x-init="if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) { setInterval(() => { i = (i + 1) % values.length; display = values[i]; }, 1500); }"
                    class="tvi-lcd relative mb-8 w-full max-w-md overflow-hidden rounded-2xl bg-slate-950 px-8 py-9 shadow-[0_0_0_1px_rgba(255,255,255,0.06),0_20px_60px_-20px_rgba(0,0,0,0.6)]"
                    aria-hidden="true"
                >
                    <p class="mb-2 font-mono text-[11px] tracking-[0.2em] text-emerald-500/70 uppercase">Faturamento estimado</p>
                    <p
                        x-text="display"
                        class="font-mono text-[34px] font-bold tabular-nums text-emerald-400 drop-shadow-[0_0_16px_rgba(52,211,153,0.55)] sm:text-[42px]"
                    >R$ 8.200 /mês</p>
                </div>

                <h1 class="mb-5 max-w-2xl text-[32px] leading-[1.12] font-extrabold tracking-tight text-balance sm:text-[46px]">
                    Toda ideia de negócio esconde um número atrás dela.
                </h1>

                <p class="mb-10 max-w-xl text-lg leading-relaxed text-white/70">
                    Responda 5 perguntas para um consultor de IA e descubra uma estimativa de faturamento mensal, o custo do seu MVP e o primeiro roteiro para tirar a ideia do papel — grátis, em menos de 3 minutos.
                </p>

                <button
                    type="button"
                    @click="chatOpen = true"
                    data-ga-event="cta_click"
                    data-ga-payload="{{ json_encode(['location' => 'tool_hero', 'label' => 'Calcular o valor da minha ideia']) }}"
                    class="inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-9 py-4 text-base font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right hover:shadow-[0_16px_36px_-12px_rgba(37,99,235,0.5)]"
                >
                    Calcular o valor da minha ideia
                </button>

                <p class="mt-4 font-mono text-[12px] tracking-wide text-white/45 uppercase">Grátis · Sem cartão · Resultado na hora</p>
            </div>
        </section>

        {{-- TEASER — mockup estático do chat (não é o componente real) cujo único papel é te fazer clicar e abrir o modal. --}}
        <section class="bg-slate-50 px-5 py-16 sm:px-8 lg:px-14">
            <div class="relative mx-auto max-w-lg">
                <div class="absolute -top-4 left-1/2 z-10 -translate-x-1/2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-[12px] font-bold text-slate-800 shadow-[0_4px_14px_-4px_rgba(15,23,42,0.35)]">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Consultor de IA online agora
                    </span>
                </div>

                <button
                    type="button"
                    @click="chatOpen = true"
                    data-ga-event="cta_click"
                    data-ga-payload="{{ json_encode(['location' => 'tool_teaser', 'label' => 'Abrir calculadora']) }}"
                    class="tvi-frame group block w-full rounded-[28px] bg-slate-900 p-3 text-left shadow-[0_30px_60px_-24px_rgba(15,23,42,0.5)] transition-transform duration-150 hover:-translate-y-0.5"
                >
                    <div class="mb-3 flex items-center gap-1.5 px-3 pt-1">
                        <span class="h-2 w-2 rounded-full bg-white/20"></span>
                        <span class="h-2 w-2 rounded-full bg-white/20"></span>
                        <span class="h-2 w-2 rounded-full bg-emerald-500/70"></span>
                    </div>

                    <div class="rounded-3xl bg-white p-5 sm:p-6">
                        <div class="flex justify-start">
                            <div class="max-w-[85%] rounded-2xl border border-slate-800/10 bg-white px-4 py-3 text-[15px] leading-relaxed text-slate-800">
                                Olá! Para começar, qual é o seu nome?
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-3 rounded-xl border border-slate-800/15 px-4 py-3 text-[15px] text-slate-400">
                            Digite sua resposta...
                            <span class="ml-auto inline-block h-4 w-[2px] animate-pulse bg-slate-400"></span>
                        </div>
                    </div>

                    <p class="px-3 py-3 text-center font-mono text-[12px] font-bold tracking-[0.14em] text-emerald-400 uppercase transition-colors group-hover:text-emerald-300">
                        Toque para abrir a calculadora ↗
                    </p>
                </button>
            </div>
        </section>

        {{-- TICKER — reforça que a calculadora serve para qualquer tipo de negócio. --}}
        <div class="overflow-hidden border-y border-slate-800/10 bg-slate-50 py-3.5" aria-hidden="true">
            <div class="tvi-marquee-track flex w-max gap-10 whitespace-nowrap">
                @foreach (array_merge($tickerItems, $tickerItems) as $item)
                    <span class="font-mono text-[13px] font-semibold tracking-wide text-slate-400 uppercase">{{ $item }} <span class="ml-10 text-slate-300">•</span></span>
                @endforeach
            </div>
        </div>

        {{-- O QUE A IA CONSIDERA — cada tecla mapeia uma pergunta do chat a um símbolo de calculadora. --}}
        <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-24">
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="mb-3 text-[26px] font-extrabold tracking-tight text-slate-800 sm:text-[32px]">Cinco perguntas. Um cálculo completo.</h2>
                <p class="mx-auto mb-12 max-w-xl text-[15px] leading-relaxed text-slate-500">Nada de formulário longo. O consultor de IA conversa com você sobre estes cinco pontos, no seu ritmo.</p>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @foreach ($keys as $key)
                        <div
                            data-reveal
                            style="transition-delay: {{ $loop->index * 60 }}ms"
                            class="flex flex-col items-center gap-3 rounded-2xl bg-slate-800 p-5 text-white shadow-[0_6px_0_0_#0f172a] transition-transform duration-150 hover:-translate-y-0.5"
                        >
                            <span class="font-mono text-2xl font-bold text-emerald-400">{{ $key['glyph'] }}</span>
                            <span class="text-center text-[13px] leading-tight font-semibold">{{ $key['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    @click="chatOpen = true"
                    data-ga-event="cta_click"
                    data-ga-payload="{{ json_encode(['location' => 'tool_keys', 'label' => 'Responder essas perguntas agora']) }}"
                    class="mt-10 inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-8 py-3.5 text-sm font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right hover:shadow-[0_16px_36px_-12px_rgba(37,99,235,0.5)]"
                >
                    Responder essas perguntas agora
                </button>
            </div>
        </section>

        {{-- COMO FUNCIONA — recibo/nota fiscal com os 3 passos do fluxo. --}}
        <section class="bg-slate-50 px-5 py-20 sm:px-8 lg:px-14 lg:py-24">
            <div class="mx-auto max-w-md rounded-lg border-2 border-dashed border-slate-300 bg-white px-8 py-8 font-mono text-slate-700 shadow-sm" data-reveal>
                <p class="mb-1 text-center text-[13px] font-bold tracking-[0.18em] text-slate-800 uppercase">Como funciona</p>
                <p class="mb-6 text-center text-[11px] tracking-wide text-slate-400">OD TEC · CALCULADORA DE IDEIAS</p>

                <div class="mb-6 border-t border-dashed border-slate-300"></div>

                <div class="space-y-5 text-[13.5px]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-bold text-slate-800">1. VOCÊ CONVERSA</p>
                            <p class="mt-1 leading-relaxed text-slate-500">Responde o consultor de IA no chat, sem formulário chato.</p>
                        </div>
                        <span class="shrink-0 font-bold text-emerald-600">✓</span>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-bold text-slate-800">2. A IA CALCULA</p>
                            <p class="mt-1 leading-relaxed text-slate-500">Cruza suas respostas com padrões reais de negócios parecidos.</p>
                        </div>
                        <span class="shrink-0 font-bold text-emerald-600">✓</span>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-bold text-slate-800">3. VOCÊ RECEBE</p>
                            <p class="mt-1 leading-relaxed text-slate-500">Faturamento estimado, custo de MVP, roteiro e PDF grátis.</p>
                        </div>
                        <span class="shrink-0 font-bold text-emerald-600">✓</span>
                    </div>
                </div>

                <div class="my-6 border-t border-dashed border-slate-300"></div>

                <div class="flex items-center justify-between text-[13px] font-bold text-slate-800">
                    <span>TOTAL</span>
                    <span>~3 MINUTOS</span>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button
                    type="button"
                    @click="chatOpen = true"
                    data-ga-event="cta_click"
                    data-ga-payload="{{ json_encode(['location' => 'tool_how_it_works', 'label' => 'Começar agora']) }}"
                    class="inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-8 py-3.5 text-sm font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right hover:shadow-[0_16px_36px_-12px_rgba(37,99,235,0.5)]"
                >
                    Começar agora
                </button>
            </div>
        </section>

        {{-- FAQ — estilo "manual/ficha técnica" da calculadora. --}}
        <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-24">
            <div class="mx-auto max-w-2xl">
                <h2 class="mb-10 text-[26px] font-extrabold tracking-tight text-slate-800 sm:text-[32px]">Manual de instruções</h2>

                <div class="flex flex-col gap-3">
                    @foreach ($faqItems as $item)
                        <details class="group rounded-xl border border-slate-800/10 bg-white open:shadow-sm" data-ga-event="faq_open" data-ga-payload="{{ json_encode(['question' => $item['question']]) }}">
                            <summary class="flex cursor-pointer list-none items-start gap-4 px-6 py-5 font-semibold text-slate-800 marker:content-none">
                                <span class="mt-0.5 shrink-0 font-mono text-[12px] font-bold text-emerald-600">Q{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="flex-1">{{ $item['question'] }}</span>
                                <svg class="mt-0.5 h-5 w-5 flex-none text-slate-400 transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </summary>
                            <div class="px-6 pb-5 pl-[3.25rem] text-[15px] leading-relaxed text-slate-500">
                                {{ $item['answer'] }}
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-5 pb-20 text-center sm:px-8">
            <button
                type="button"
                @click="chatOpen = true"
                data-ga-event="cta_click"
                data-ga-payload="{{ json_encode(['location' => 'tool_footer', 'label' => 'Calcular o valor da minha ideia']) }}"
                class="inline-block rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-9 py-4 text-base font-bold text-white transition-[background-position,transform] duration-300 ease-out hover:-translate-y-0.5 hover:bg-right hover:shadow-[0_16px_36px_-12px_rgba(37,99,235,0.5)]"
            >
                Calcular o valor da minha ideia
            </button>
        </section>

        {{-- CTA FLUTUANTE — aparece depois que a pessoa passa da primeira dobra, pra quem rolou direto sem clicar. --}}
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
                class="inline-flex items-center gap-2.5 rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_28px_-8px_rgba(37,99,235,0.55)] transition-[background-position,transform] duration-300 hover:-translate-y-0.5 hover:bg-right"
            >
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                </span>
                Calcular agora
            </button>
        </div>

        {{-- MODAL — o chat compartilhado de verdade vive só aqui, em tela cheia, travando o scroll da página até fechar. --}}
        <div
            x-show="chatOpen"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-3 backdrop-blur-sm sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-label="Calculadora: quanto vale sua ideia"
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
                        aria-label="Fechar calculadora"
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
