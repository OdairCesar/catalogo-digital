@props([
    'eyebrow' => null,
    'title',
])

<section {{ $attributes->class(['px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <div class="mx-auto grid max-w-[1180px] gap-10 sm:gap-16 {{ isset($aside) ? 'min-[960px]:grid-cols-[1.1fr_0.9fr] min-[960px]:items-start' : '' }}">
        <div data-reveal>
            <x-ui.section-title :eyebrow="$eyebrow">{{ $title }}</x-ui.section-title>

            <div class="mt-5 space-y-4 text-base leading-relaxed text-slate-500">
                {{ $slot }}
            </div>
        </div>

        @isset($aside)
            <div data-reveal style="transition-delay: 120ms" class="flex flex-col gap-5">
                {{ $aside }}
            </div>
        @endisset
    </div>
</section>
