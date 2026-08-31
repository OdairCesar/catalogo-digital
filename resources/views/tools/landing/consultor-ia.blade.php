@use('App\Services\Seo\StructuredDataService')

@php
    $breadcrumbs = [
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Ferramentas', 'url' => route('tools.index')],
        ['label' => $tool->title],
    ];

    $jsonLd = [app(StructuredDataService::class)->breadcrumbList($breadcrumbs)];
@endphp

{{--
    ESTRUTURA DESTA PÁGINA (5ª ferramenta, conceito próprio): só o CONVITE DE
    AGENDA — cartão claro estilo "evento de calendário", sem mais nenhuma
    seção. O modal (ao aceitar o convite) é que vira a "chamada" escura.
--}}
<x-layout.tool :title="$tool->title.' — OD Tec'" :description="$tool->description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <style>
        @keyframes tvi-invite-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0.18); }
            50% { box-shadow: 0 0 0 14px rgba(37,99,235,0); }
        }
        .tvi-invite-card {
            animation: tvi-invite-glow 2.6s ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .tvi-invite-card {
                animation: none;
            }
        }
    </style>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        {{-- CONVITE — o card de agenda ocupa a tela inteira; é ele mesmo o gatilho do modal. --}}
        <section class="flex min-h-screen items-center justify-center bg-slate-50 px-5 py-16 sm:px-8">
            <button
                type="button"
                @click="chatOpen = true"
                data-ga-event="cta_click"
                data-ga-payload="{{ json_encode(['location' => 'tool_invite', 'label' => 'Aceitar e entrar']) }}"
                class="tvi-invite-card group w-full max-w-md rounded-[28px] border border-slate-800/10 bg-white p-8 text-left shadow-[0_30px_70px_-30px_rgba(15,23,42,0.25)] transition-transform duration-150 hover:-translate-y-0.5 sm:p-10"
            >
                <p class="mb-6 font-mono text-[11px] font-bold tracking-[0.16em] text-blue-600 uppercase">Convite de agenda</p>

                <h1 class="mb-2 text-[26px] font-extrabold tracking-tight text-slate-800 sm:text-[30px]">Consultoria de Projeto com IA</h1>
                <p class="mb-6 text-[15px] text-slate-500">Hoje · Agora mesmo · Cerca de 10 minutos · Online</p>

                <div class="mb-6 flex items-center gap-3 border-y border-slate-800/10 py-4">
                    <div class="flex -space-x-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-slate-800 text-xs font-bold text-white">Vc</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-600 to-emerald-500 text-xs font-bold text-white">IA</span>
                    </div>
                    <p class="text-[13.5px] text-slate-500">Você e o Consultor de IA da OD Tec</p>
                </div>

                <p class="mb-6 text-[14.5px] leading-relaxed text-slate-600">Conte sua ideia de projeto, sistema ou aplicativo. Sem roteiro fixo — o consultor pergunta o que for preciso e, ao final, entrega um relatório completo de escopo e investimento.</p>

                <div class="flex items-center justify-center gap-2 rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 bg-[length:200%_200%] bg-left px-6 py-3.5 text-sm font-bold text-white transition-[background-position] duration-300 group-hover:bg-right">
                    Aceitar e entrar na consultoria
                </div>

                <p class="mt-4 text-center font-mono text-[11px] tracking-wide text-slate-400 uppercase">Grátis · Sem cartão · Sem roteiro fixo</p>
            </button>
        </section>

        {{-- MODAL — ao aceitar o convite, a chamada abre em tela cheia, com o componente Livewire original do Consultor de IA. --}}
        <div
            x-show="chatOpen"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-3 backdrop-blur-sm sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-label="Consultor de IA"
        >
            <div
                @click.outside="chatOpen = false"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative flex h-full max-h-[720px] w-full max-w-lg flex-col rounded-[28px] bg-slate-900 p-3 shadow-[0_40px_80px_-20px_rgba(0,0,0,0.6)]"
            >
                <div class="mb-2 flex items-center justify-between px-3 pt-1">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-red-400/70"></span>
                        <span class="font-mono text-[11px] tracking-[0.14em] text-white/50 uppercase">Chamada em andamento</span>
                    </div>
                    <button
                        type="button"
                        @click="chatOpen = false"
                        aria-label="Encerrar consultoria"
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
