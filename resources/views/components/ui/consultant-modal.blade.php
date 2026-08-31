{{--
    Modal do Consultor de IA reutilizável fora da landing de /ferramentas. Requer um
    ancestral com x-data="{ chatOpen: false }" (é ele quem controla a abertura/fechamento);
    este componente só cuida da apresentação do modal em si.
--}}
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
            <livewire:tool-chat :slug="'consultor-ia'" :key="'consultor-ia-modal'" />
        </div>
    </div>
</div>
