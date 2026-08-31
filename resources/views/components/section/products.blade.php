@props([
    'title',
    'description' => null,
    'items' => [],
])

<section {{ $attributes->class(['mx-auto max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)] pt-[clamp(18px,2.2vw,32px)] pb-2']) }}>
    <div class="flex items-baseline justify-between gap-3">
        <h2 class="font-display text-[clamp(21px,2.6vw,32px)] font-bold tracking-[-0.015em] text-brand-navy">{{ $title }}</h2>

        <a href="{{ route('products.index') }}" title="Ver todos os produtos" class="font-display shrink-0 text-[clamp(13px,1.3vw,14.5px)] font-semibold whitespace-nowrap">ver tudo</a>
    </div>

    @if ($description)
        <p class="mt-[6px] max-w-[560px] text-[clamp(13.5px,1.4vw,16px)] leading-[1.5] text-[#6B7688]">{{ $description }}</p>
    @endif

    <div class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(min(46%,250px),1fr))] gap-[clamp(14px,1.6vw,22px)]">
        @foreach ($items as $item)
            <x-ui.product-card
                :title="$item['title']"
                :url="$item['url']"
                :cover-image-url="$item['coverImageUrl']"
                :price-label="$item['priceLabel']"
                :installment-label="$item['installmentLabel']"
                :stock-label="$item['stockLabel']"
                :tag="$item['tag']"
                :grade-label="$item['gradeLabel']"
            />
        @endforeach
    </div>
</section>
