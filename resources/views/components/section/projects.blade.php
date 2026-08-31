@props([
    'eyebrow' => null,
    'title',
    'items' => [],
])

<section {{ $attributes->class(['bg-slate-50 px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <div class="mx-auto max-w-[1180px]">
        <x-ui.section-title :eyebrow="$eyebrow" titleClass="text-[28px] sm:text-[38px]" class="mb-14" data-reveal>
            {{ $title }}
        </x-ui.section-title>

        <div class="grid gap-6 min-[640px]:grid-cols-2 min-[960px]:grid-cols-3">
            @foreach ($items as $index => $item)
                @php $tag = empty($item['url']) ? 'div' : 'a'; @endphp
                <{{ $tag }}
                    @if (! empty($item['url']))
                        href="{{ $item['url'] }}"
                        title="{{ $item['title'] }}"
                        data-ga-event="project_click"
                        data-ga-payload="{{ json_encode(['title' => $item['title']]) }}"
                    @endif
                    data-reveal
                    style="transition-delay: {{ $index * 120 }}ms"
                    class="group block"
                >
                    @if (! empty($item['image']))
                        <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" loading="lazy" decoding="async" class="mb-4 h-[220px] w-full rounded-2xl object-cover" />
                    @else
                        <div class="mb-4 flex h-[220px] w-full items-center justify-center rounded-2xl bg-gradient-to-br from-slate-200 to-slate-100 text-sm font-medium text-slate-400">
                            {{ $item['title'] }}
                        </div>
                    @endif
                    <h4 class="mb-1.5 text-[17px] font-bold group-hover:text-blue-600">{{ $item['title'] }}</h4>
                    <p class="text-sm text-slate-500">{{ $item['desc'] }}</p>
                </{{ $tag }}>
            @endforeach
        </div>
    </div>
</section>
