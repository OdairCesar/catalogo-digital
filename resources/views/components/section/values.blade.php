@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'items' => [],
])

<section {{ $attributes->class(['px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <div class="mx-auto max-w-[1180px]">
        <x-ui.section-title :eyebrow="$eyebrow" :description="$description" titleClass="text-[28px] sm:text-[38px]" class="mb-12 max-w-2xl" data-reveal>
            {{ $title }}
        </x-ui.section-title>

        <div data-reveal style="transition-delay: 100ms">
            <x-ui.carousel :pagination="count($items) > 1" :options="['slidesPerView' => 'auto', 'spaceBetween' => 20]">
                @foreach ($items as $item)
                    <div class="swiper-slide w-[280px] sm:w-[320px]">
                        <div class="flex h-full items-start gap-4 rounded-2xl border border-slate-800/10 bg-white p-6">
                            <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-purple text-white">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </span>
                            <div>
                                <h4 class="font-display mb-1 text-[16.5px] font-bold">{{ $item['title'] }}</h4>
                                <p class="text-[14.5px] leading-relaxed text-slate-500">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-ui.carousel>
        </div>
    </div>
</section>
