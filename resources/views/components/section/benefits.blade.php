@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'items' => [],
])

<section {{ $attributes->class(['bg-slate-50 px-5 py-20 sm:px-8 lg:px-14 lg:py-28']) }}>
    <div class="mx-auto max-w-6xl">
        <x-ui.section-title :eyebrow="$eyebrow" :description="$description" class="mb-12 max-w-2xl" data-reveal>
            {{ $title }}
        </x-ui.section-title>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($items as $index => $item)
                <div
                    data-reveal
                    style="transition-delay: {{ $index * 60 }}ms"
                    class="flex items-start gap-4 rounded-2xl border border-slate-800/10 bg-white p-6"
                >
                    <span class="flex h-8 w-8 flex-none items-center justify-center rounded-[10px] bg-gradient-to-br from-blue-600 to-emerald-500 text-white">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </span>
                    <p class="text-[15px] leading-relaxed text-slate-800">{{ $item }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
