@props([
    'title',
    'excerpt' => null,
    'url',
    'coverImageUrl' => null,
    'categoryLabel' => null,
    'publishedAt' => null,
    'priceLabel' => null,
])

<x-ui.card {{ $attributes }}>
    @if ($coverImageUrl)
        <img src="{{ $coverImageUrl }}" alt="{{ $title }}" loading="lazy" class="-mx-8 -mt-8 mb-2 aspect-[16/9] w-[calc(100%_+_4rem)] max-w-none rounded-t-[20px] object-cover">
    @endif

    @if ($categoryLabel)
        <span class="text-sm font-bold tracking-wide text-brand-purple uppercase">{{ $categoryLabel }}</span>
    @endif

    <a href="{{ $url }}" title="{{ $title }}" class="font-display text-lg font-bold text-slate-800 after:absolute after:inset-0 hover:text-brand-purple">{{ $title }}</a>

    @if ($excerpt)
        <p class="text-[15px] leading-relaxed text-slate-500">{{ $excerpt }}</p>
    @endif

    @if ($priceLabel)
        <span class="text-lg font-extrabold text-slate-800">{{ $priceLabel }}</span>
    @endif

    @if ($publishedAt)
        <time datetime="{{ $publishedAt->toAtomString() }}" class="text-xs text-slate-400">{{ $publishedAt->translatedFormat('d \d\e F \d\e Y') }}</time>
    @endif

    <a href="{{ $url }}" title="Ler mais sobre {{ $title }}" class="relative text-sm font-bold text-brand-purple">Ler mais &rarr;</a>
</x-ui.card>
