@props([
    'eyebrow' => null,
    'title',
    'items' => [],
])

<div class="mt-[clamp(28px,3.4vw,56px)] bg-brand-lilac" {{ $attributes }}>
    <div class="mx-auto max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)] pt-[clamp(28px,3.6vw,56px)] pb-[clamp(30px,3.6vw,56px)]">
        @if ($eyebrow)
            <div class="font-display text-[11px] font-semibold tracking-[0.1em] text-[#8B4FA6] uppercase">{{ $eyebrow }}</div>
        @endif

        <h2 class="font-display mt-[8px] max-w-[18ch] text-[clamp(22px,2.8vw,34px)] leading-[1.15] font-bold tracking-[-0.015em] text-[#3D1A4C]">{!! $title !!}</h2>

        <div class="mt-[18px]">
            <x-ui.carousel :options="[
                'slidesPerView' => 'auto',
                'spaceBetween' => 18,
                'breakpoints' => [768 => ['slidesPerView' => 3, 'spaceBetween' => 20]],
            ]">
                @foreach ($items as $item)
                    <div data-reveal class="swiper-slide w-[264px] rounded-[22px] bg-brand-offwhite p-[clamp(18px,2vw,24px)] shadow-[0_4px_16px_rgba(61,26,76,0.10)]">
                        <p class="text-[clamp(14.5px,1.4vw,16px)] leading-[1.6] text-[#2A3446]">{{ $item['text'] }}</p>
                        <div class="mt-[16px] flex items-center gap-[10px]">
                            <div class="font-display flex h-[56px] w-[56px] flex-none items-center justify-center rounded-full bg-brand-purple text-lg font-bold text-white">{{ $item['initial'] }}</div>
                            <div>
                                <div class="font-display text-[13.5px] font-semibold text-brand-navy">{{ $item['name'] }}</div>
                                <div class="text-[12px] text-[#8A93A3]">{{ $item['detail'] }}</div>
                            </div>
                        </div>
                        <x-section.extra-fields :fields="$item['extra_fields'] ?? []" />
                    </div>
                @endforeach
            </x-ui.carousel>
        </div>
    </div>
</div>
