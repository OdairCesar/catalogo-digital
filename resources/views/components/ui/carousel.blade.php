@props([
    'pagination' => false,
    'options' => [],
])

@php
    $swiperOptions = array_merge([
        'slidesPerView' => 'auto',
        'spaceBetween' => 16,
    ], $options);
@endphp

<div
    {{ $attributes->class(['swiper', $pagination ? 'pb-10' : '']) }}
    data-swiper
    data-swiper-options="{{ json_encode($swiperOptions) }}"
>
    <div class="swiper-wrapper">
        {{ $slot }}
    </div>

    @if ($pagination)
        <div class="swiper-pagination"></div>
    @endif
</div>
