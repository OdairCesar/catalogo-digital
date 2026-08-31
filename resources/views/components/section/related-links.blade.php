@props([
    'links' => [],
    'title' => 'Você também pode se interessar',
    'eyebrow' => 'Continue explorando',
])

@if (! empty($links))
    <section class="bg-slate-50 px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title :eyebrow="$eyebrow" class="mb-10">{{ $title }}</x-ui.section-title>

            <div class="flex flex-wrap gap-3">
                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}" title="{{ $link['label'] }}" class="rounded-full border border-slate-800/10 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 hover:text-blue-600">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
