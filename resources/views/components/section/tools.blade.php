@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'items' => [],
])

{{--
    Cartão deliberadamente diferente dos demais no site (sem icon-badge, sem
    foto): cada ferramenta vira uma "linha de busca" em formato pílula, com um
    botão de seta que gira 45° no hover — reforçando que é uma pergunta que
    leva a uma resposta imediata via IA.
--}}
<section {{ $attributes->class(['bg-slate-50 px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <div class="mx-auto max-w-[1180px]">
        <x-ui.section-title :eyebrow="$eyebrow" :description="$description" titleClass="text-[28px] sm:text-[38px]" class="mx-auto mb-12 max-w-2xl text-center" data-reveal>
            {{ $title }}
        </x-ui.section-title>

        <div class="mx-auto flex max-w-3xl flex-col gap-3">
            @foreach ($items as $index => $item)
                <a
                    href="{{ $item['url'] }}"
                    title="{{ $item['title'] }}"
                    target="_blank"
                    rel="noopener"
                    data-reveal
                    style="transition-delay: {{ $index * 70 }}ms"
                    data-ga-event="cta_click"
                    data-ga-payload="{{ json_encode(['location' => 'home_tools', 'label' => $item['title']]) }}"
                    class="group flex items-center gap-4 rounded-full border border-slate-800/10 bg-white py-3 pr-3 pl-7 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1 hover:border-blue-600/25 hover:shadow-[0_20px_40px_-16px_rgba(37,99,235,0.22)]"
                >
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[15.5px] font-bold text-slate-800">{{ $item['title'] }}</span>
                        <span class="block truncate text-[13px] text-slate-400">{{ $item['tagline'] }}</span>
                    </span>

                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-emerald-500 text-white transition-transform duration-300 group-hover:rotate-45 group-hover:scale-110">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </span>
                </a>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('tools.index') }}" title="Ver todas as ferramentas" class="text-sm font-bold text-blue-600 hover:text-blue-700">Ver todas as ferramentas &rarr;</a>
        </div>
    </div>
</section>
