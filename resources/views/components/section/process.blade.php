@props([
    'eyebrow' => null,
    'title',
    'steps' => [],
])

<section {{ $attributes->class(['bg-slate-50 px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <div class="mx-auto max-w-[1180px]">
        <x-ui.section-title :eyebrow="$eyebrow" titleClass="text-[28px] sm:text-[38px]" class="mb-14" data-reveal>
            {{ $title }}
        </x-ui.section-title>

        <div class="grid gap-6 min-[640px]:grid-cols-2 min-[960px]:grid-cols-4">
            @foreach ($steps as $index => $step)
                <div data-reveal style="transition-delay: {{ $index * 80 }}ms">
                    <div class="mb-4 text-4xl font-extrabold text-blue-600">{{ $step['n'] }}</div>
                    <h3 class="mb-2.5 text-lg font-bold">{{ $step['title'] }}</h3>
                    <p class="text-[14.5px] leading-relaxed text-slate-500">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
