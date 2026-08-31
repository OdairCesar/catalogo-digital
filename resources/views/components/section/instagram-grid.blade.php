@props([
    'title',
    'description' => null,
    'items' => [],
    'count' => 6,
])

@php
    $tiles = ['from-brand-purple to-brand-purple-dark', 'from-brand-magenta to-brand-purple', 'from-brand-navy to-slate-600', 'from-brand-purple-dark to-brand-navy', 'from-brand-purple to-brand-magenta', 'from-slate-600 to-brand-navy'];
@endphp

<section {{ $attributes->class(['mx-auto max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)] pt-[clamp(30px,3.6vw,56px)] pb-[6px]']) }}>
    <h2 class="font-display text-[clamp(21px,2.6vw,32px)] font-bold tracking-[-0.015em] text-brand-navy">{{ $title }}</h2>

    @if ($description)
        <p class="mt-[6px] text-[clamp(13.5px,1.4vw,16px)] leading-[1.5] text-[#6B7688]">{!! $description !!}</p>
    @endif

    <div class="mt-[16px]">
        <x-ui.carousel :options="[
            'slidesPerView' => 2.3,
            'spaceBetween' => 10,
            'grid' => ['rows' => 2, 'fill' => 'row'],
            'breakpoints' => [768 => ['slidesPerView' => 6, 'spaceBetween' => 12, 'grid' => ['rows' => 1, 'fill' => 'row']]],
        ]">
            @forelse ($items as $item)
                @if (!empty($item['link']))
                    <a href="{{ $item['link'] }}" target="_blank" rel="noopener" data-reveal class="swiper-slide block aspect-square overflow-hidden rounded-[14px]">
                        <img src="{{ $item['imageUrl'] }}" alt="{{ $item['caption'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                    </a>
                @else
                    <div data-reveal class="swiper-slide aspect-square overflow-hidden rounded-[14px]">
                        <img src="{{ $item['imageUrl'] }}" alt="{{ $item['caption'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                    </div>
                @endif
            @empty
                @for ($i = 0; $i < $count; $i++)
                    <div data-reveal class="swiper-slide aspect-square overflow-hidden rounded-[14px] bg-gradient-to-br {{ $tiles[$i % count($tiles)] }}"></div>
                @endfor
            @endforelse
        </x-ui.carousel>
    </div>
</section>
