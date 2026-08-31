@props([
    'href' => '#',
    'variant' => 'primary',
    'newTab' => false,
    'gaEvent' => null,
    'gaPayload' => null,
    'modal' => false,
])

@php
    $variants = [
        'primary' => 'text-white bg-brand-purple transition-[background-color_.25s_ease,transform_.25s_ease,box-shadow_.25s_ease] hover:bg-brand-purple-light hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-10px_rgba(150,71,178,0.45)]',
        'outline-light' => 'text-slate-800 border border-slate-800/20 transition-transform duration-200 hover:-translate-y-0.5',
        'outline-dark' => 'text-white border border-white/30 transition-transform duration-200 hover:-translate-y-0.5',
    ];

    $linkTitle = trim(strip_tags($slot));
@endphp

<a
    href="{{ $href }}"
    @if ($linkTitle !== '') title="{{ $linkTitle }}" @endif
    @if ($newTab) target="_blank" rel="noopener noreferrer" @endif
    @if ($gaEvent) data-ga-event="{{ $gaEvent }}" @endif
    @if ($gaPayload) data-ga-payload="{{ json_encode($gaPayload) }}" @endif
    @if ($modal) @click.prevent="chatOpen = true" @endif
    {{ $attributes->class(['inline-block rounded-full px-8 py-4 font-display text-base font-bold text-center', $variants[$variant] ?? $variants['primary']]) }}
>{{ $slot }}</a>
