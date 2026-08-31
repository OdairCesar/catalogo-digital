@props([
    'title',
    'url',
    'coverImageUrl' => null,
    'priceLabel' => null,
    'stockLabel' => null,
    'tag' => null,
    'gradeLabel' => null,
    'installmentLabel' => null,
])

<div {{ $attributes->class(['group relative flex flex-col overflow-hidden rounded-[clamp(20px,2vw,24px)] bg-white shadow-[0_3px_14px_rgba(28,40,57,0.07)] transition-shadow duration-300 hover:shadow-[0_10px_26px_rgba(28,40,57,0.14)]']) }} data-reveal>
    <div class="relative h-[clamp(196px,25vw,330px)] w-full overflow-hidden bg-slate-100">
        @if ($coverImageUrl)
            <img src="{{ $coverImageUrl }}" alt="{{ $title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.055]">
        @else
            <div class="flex h-full w-full items-center justify-center text-sm font-medium text-slate-400">{{ $title }}</div>
        @endif

        @if ($tag)
            <span class="font-display absolute top-[9px] left-[9px] rounded-full bg-brand-offwhite/94 px-[9px] py-[4px] text-[clamp(10px,1vw,11px)] font-semibold text-brand-purple-dark">{{ $tag }}</span>
        @endif

        @if ($stockLabel === 'Fora de estoque')
            <span class="font-display absolute top-[9px] right-[9px] rounded-full bg-brand-navy px-[9px] py-[4px] text-[clamp(10px,1vw,11px)] font-bold text-white">Esgotado</span>
        @endif
    </div>

    <a href="{{ $url }}" title="{{ $title }}" class="flex flex-1 flex-col px-[clamp(12px,1.3vw,18px)] pt-[11px] pb-[clamp(14px,1.6vw,20px)] after:absolute after:inset-0">
        <span class="font-display text-[clamp(13.5px,1.4vw,16.5px)] leading-[1.25] font-semibold text-brand-navy">{{ $title }}</span>

        @if ($gradeLabel)
            <span class="mt-[3px] text-[clamp(11.5px,1.2vw,13px)] text-[#8A93A3]">{{ $gradeLabel }}</span>
        @endif

        @if ($priceLabel)
            <span class="font-display mt-[8px] text-[clamp(16px,1.7vw,20px)] font-bold text-brand-purple">{{ $priceLabel }}</span>
        @endif

        @if ($installmentLabel)
            <span class="mt-[2px] text-[clamp(11px,1.1vw,12.5px)] text-[#8A93A3]">{{ $installmentLabel }}</span>
        @endif
    </a>
</div>
