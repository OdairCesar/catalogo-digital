@props([
    'delay' => '0ms',
    'href' => null,
    'title' => null,
])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    @if ($href && $title) title="{{ $title }}" @endif
    data-reveal
    style="transition-delay: {{ $delay }}"
    {{ $attributes->class(['group relative flex flex-col gap-4 rounded-[20px] border border-slate-800/10 bg-white p-8 transition-[transform,box-shadow] duration-300 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_-16px_rgba(30,41,59,0.18)]']) }}
>
    {{ $slot }}
</{{ $tag }}>
