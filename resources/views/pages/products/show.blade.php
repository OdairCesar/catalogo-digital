@php
    $waBase = $company?->whatsappLink();
    $waMessage = "Oi Cae! Quero saber mais sobre: {$vm->title}";
    $waLink = $waBase !== null ? $waBase.'?text='.rawurlencode($waMessage) : null;

    $images = collect([$vm->coverImageUrl, ...$vm->galleryImageUrls])->filter()->unique()->values();
    $hasTwoAxis = ! empty($vm->sizeOptions) && ! empty($vm->colorOptions);
    $hasColorOnly = empty($vm->sizeOptions) && ! empty($vm->colorOptions);
@endphp

<x-layout.app :title="$vm->seo->title" :description="$vm->seo->description" :canonical="$vm->seo->canonical" :robots="$vm->seo->robots" :og-image="$vm->coverImageUrl" og-type="product" :og-product="[
    'price' => $vm->price,
    'currency' => 'BRL',
    'availability' => $vm->availability,
    'brand' => $vm->brand,
]">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$vm->jsonLd" />
    </x-slot:jsonLd>

    <article
        data-wa-base="{{ $waBase }}"
        data-product-title="{{ $vm->title }}"
        @if ($hasTwoAxis || $hasColorOnly)
            data-product-two-axis
            data-matrix="{{ json_encode($vm->variantMatrix) }}"
        @elseif (! empty($vm->variants))
            data-product-variants
        @endif
    >
        <div class="mx-auto flex max-w-[1240px] flex-wrap items-start gap-x-[clamp(24px,3vw,48px)]">
            {{-- Gallery: main image with a horizontal thumbnail row below it --}}
            <div class="min-w-0 flex-[1_1_min(100%,440px)]">
                <div class="relative h-[clamp(440px,52vw,660px)] bg-slate-100">
                    @if ($images->isNotEmpty())
                        <img data-product-image src="{{ $images->first() }}" alt="{{ $vm->title }}" class="h-full w-full object-cover">
                    @endif

                    <a href="{{ $vm->breadcrumbs[count($vm->breadcrumbs) - 2]['url'] ?? route('products.index') }}" title="Voltar" class="absolute top-[14px] left-[14px] grid h-[38px] w-[38px] place-items-center rounded-full bg-white/95 text-[16px] text-brand-navy shadow-[0_4px_12px_rgba(28,40,57,0.18)]">&larr;</a>

                    @if ($images->count() > 1)
                        <span data-product-image-counter data-total="{{ $images->count() }}" class="absolute right-[14px] bottom-[14px] rounded-full bg-brand-navy/78 px-[10px] py-[5px] text-[11.5px] text-white">1 / {{ $images->count() }}</span>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <div class="mt-[10px] flex gap-[8px] px-[clamp(18px,3vw,0px)]">
                        @foreach ($images as $index => $imageUrl)
                            <button type="button" data-product-thumb data-image="{{ $imageUrl }}" data-index="{{ $index }}" class="aspect-square flex-1 overflow-hidden rounded-xl">
                                <img src="{{ $imageUrl }}" alt="{{ $vm->title }}" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info column --}}
            <div class="min-w-0 flex-[1_1_min(100%,380px)] pb-[clamp(0px,1vw,8px)]">
                <div class="px-[clamp(18px,3vw,24px)] pt-[20px]">
                    @if ($vm->categoryName)
                        <span class="font-display inline-flex rounded-full bg-brand-lilac px-[10px] py-[5px] text-[10.5px] font-semibold tracking-[0.06em] text-brand-purple-dark uppercase">{{ $vm->categoryName }}</span>
                    @endif

                    <h1 class="font-display mt-[10px] text-[clamp(25px,3.2vw,38px)] leading-[1.15] font-bold tracking-[-0.022em] text-balance text-brand-navy">{{ $vm->title }}</h1>

                    <div class="mt-[10px] flex flex-wrap items-baseline gap-[10px]">
                        @if ($vm->priceLabel)
                            <span data-product-price class="font-display text-[clamp(27px,3.2vw,36px)] font-extrabold text-brand-purple">{{ $vm->priceLabel }}</span>
                        @endif

                        @if ($vm->salePriceLabel && $vm->salePriceLabel !== $vm->priceLabel)
                            <span class="text-[clamp(13px,1.3vw,15px)] text-slate-400 line-through">{{ $vm->salePriceLabel }}</span>
                        @endif
                    </div>

                    @if ($vm->installmentLabel)
                        <div class="mt-[3px] text-[clamp(12.5px,1.3vw,14px)] text-slate-500">{{ $vm->installmentLabel }} · Pix com 5% off</div>
                    @endif

                    @if ($vm->description)
                        <p class="mt-[14px] text-[clamp(14.5px,1.5vw,16.5px)] leading-[1.65] text-[#3A4557]">{{ $vm->description }}</p>
                    @endif
                </div>

                @if ($hasTwoAxis || $hasColorOnly)
                    @if ($hasTwoAxis)
                        <div class="px-[clamp(18px,3vw,24px)] pt-[22px]">
                            <div class="flex items-baseline justify-between">
                                <div class="font-display text-[clamp(14px,1.4vw,15px)] font-semibold text-brand-navy">Tamanho</div>
                                @if ($waBase)
                                    <a href="{{ $waBase }}?text={{ rawurlencode('Oi Cae! Quais as medidas reais das peças?') }}" target="_blank" rel="noopener" class="text-[12.5px] font-semibold">medidas reais &rarr;</a>
                                @endif
                            </div>
                            <div class="mt-[11px] flex flex-wrap gap-[8px]">
                                @foreach ($vm->sizeOptions as $index => $size)
                                    <button type="button" data-size-option data-label="{{ $size['label'] }}" class="{{ $index === 0 ? 'is-selected' : '' }} font-display min-w-[52px] rounded-[13px] border-[1.5px] border-slate-800/15 px-[8px] py-[12px] text-[13.5px] font-semibold text-brand-navy">{{ $size['label'] }}</button>
                                @endforeach
                            </div>
                            <div class="mt-[10px] text-[12.5px] text-slate-500">Na dúvida entre dois, pega o maior 💜</div>
                        </div>
                    @endif

                    <div class="px-[clamp(18px,3vw,24px)] {{ $hasTwoAxis ? 'pt-[20px]' : 'pt-[22px]' }}">
                        <div class="font-display text-[clamp(14px,1.4vw,15px)] font-semibold text-brand-navy">Cor</div>
                        <div class="mt-[11px] flex flex-wrap gap-[10px]">
                            @foreach ($vm->colorOptions as $index => $color)
                                <button type="button" data-color-option data-label="{{ $color['label'] }}" class="{{ $index === 0 ? 'is-selected' : '' }} font-display flex items-center gap-[8px] rounded-full border-[1.5px] border-slate-800/15 py-[9px] pr-[14px] pl-[10px] text-[13.5px] font-semibold text-brand-navy">
                                    <span
                                        class="h-[20px] w-[20px] rounded-full bg-cover bg-center shadow-[inset_0_0_0_1px_rgba(28,40,57,0.12)]"
                                        style="background:{{ $color['imageUrl'] ? "url('{$color['imageUrl']}') center/cover" : $color['hex'] }}"
                                    ></span>
                                    {{ $color['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @elseif (! empty($vm->variants))
                    <div class="px-[clamp(18px,3vw,24px)] pt-[22px]">
                        <div class="font-display text-[clamp(14px,1.4vw,15px)] font-semibold text-brand-navy">Escolha a variação</div>
                        <div class="mt-[11px] flex flex-wrap gap-[8px]">
                            @foreach ($vm->variants as $index => $variant)
                                <button
                                    type="button"
                                    data-variant
                                    data-label="{{ $variant['label'] }}"
                                    data-price-label="{{ $variant['priceLabel'] }}"
                                    data-stock-label="{{ $variant['stockLabel'] }}"
                                    data-image="{{ $variant['imageUrl'] }}"
                                    @disabled(! $variant['inStock'])
                                    class="{{ $index === 0 ? 'is-selected' : '' }} font-display rounded-full border border-slate-800/15 px-5 py-2.5 text-sm font-semibold text-brand-navy disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    {{ $variant['label'] }}
                                    @unless ($variant['inStock'])
                                        <span class="text-slate-400">(esgotado)</span>
                                    @endunless
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-[16px] px-[clamp(18px,3vw,24px)] text-[12.5px] text-slate-500">
                    <span data-product-stock>{{ $vm->stockLabel }}</span>
                    @if ($vm->brand)
                        · {{ $vm->brand }}
                    @endif
                    · {{ $vm->conditionLabel }}
                </div>

                <div class="mx-[clamp(16px,3vw,24px)] mt-[22px] overflow-hidden rounded-[22px] border border-brand-lilac bg-brand-offwhite">
                    <div class="flex gap-[14px] border-b border-brand-lilac p-[16px]">
                        <span class="text-[19px]" aria-hidden="true">&harr;</span>
                        <div>
                            <div class="font-display text-[14.5px] font-semibold text-brand-navy">Caimento honesto</div>
                            <div class="mt-[3px] text-[13.5px] leading-[1.55] text-slate-500">Veste fiel. Se você tá entre dois tamanhos, pega o maior — o tecido abraça mesmo.</div>
                        </div>
                    </div>
                    <div class="flex gap-[14px] border-b border-brand-lilac p-[16px]">
                        <span class="text-[19px]" aria-hidden="true">&olarr;</span>
                        <div>
                            <div class="font-display text-[14.5px] font-semibold text-brand-navy">Troca sem drama</div>
                            <div class="mt-[3px] text-[13.5px] leading-[1.55] text-slate-500">Não serviu? Me chama em até 7 dias e a gente resolve juntas.</div>
                        </div>
                    </div>
                    <div class="flex gap-[14px] p-[16px]">
                        <span class="text-[19px]" aria-hidden="true">&#10047;</span>
                        <div>
                            <div class="font-display text-[14.5px] font-semibold text-brand-navy">Testado pela Cae</div>
                            <div class="mt-[3px] text-[13.5px] leading-[1.55] text-slate-500">Treinei semanas com essa peça antes de trazer pra loja.</div>
                        </div>
                    </div>
                </div>

                @if (! empty($vm->testimonials))
                    <div class="mx-[clamp(16px,3vw,24px)] mt-[22px] rounded-[22px] bg-brand-lilac p-[18px]">
                        <p class="text-[clamp(14.5px,1.4vw,16px)] leading-[1.6] text-balance text-brand-purple-dark">"{{ $vm->testimonials[0]['text'] }}"</p>
                        <div class="mt-[14px] flex items-center gap-[10px]">
                            <div class="font-display flex h-[56px] w-[56px] flex-none items-center justify-center rounded-full bg-brand-purple text-sm font-bold text-white">{{ $vm->testimonials[0]['initial'] }}</div>
                            <div class="font-display text-[12.5px] font-semibold text-brand-purple-dark">{{ $vm->testimonials[0]['name'] }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if (! empty($vm->relatedProducts))
            <div class="mx-auto mt-[clamp(26px,3.4vw,56px)] max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)]">
                <h2 class="font-display mb-[14px] text-[clamp(19px,2.4vw,30px)] font-bold tracking-[-0.015em] text-brand-navy">Combina com você também</h2>
                <x-ui.carousel :options="[
                    'slidesPerView' => 'auto',
                    'spaceBetween' => 18,
                    'breakpoints' => [768 => ['slidesPerView' => 3, 'spaceBetween' => 20]],
                ]">
                    @foreach ($vm->relatedProducts as $related)
                        <a href="{{ $related['url'] }}" title="{{ $related['title'] }}" class="swiper-slide w-[255px]">
                            <div class="h-[clamp(170px,20vw,280px)] overflow-hidden rounded-[18px] bg-slate-100">
                                @if ($related['coverImageUrl'])
                                    <img src="{{ $related['coverImageUrl'] }}" alt="{{ $related['title'] }}" loading="lazy" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="font-display mt-[8px] text-[clamp(12.5px,1.3vw,15px)] leading-[1.3] font-semibold text-brand-navy">{{ $related['title'] }}</div>
                            @if ($related['priceLabel'])
                                <div class="font-display mt-[3px] text-[clamp(13.5px,1.4vw,17px)] font-bold text-brand-purple">{{ $related['priceLabel'] }}</div>
                            @endif
                        </a>
                    @endforeach
                </x-ui.carousel>
            </div>
        @endif
    </article>

    <div data-product-sticky-bar class="sticky bottom-0 z-20 border-t border-brand-lilac bg-brand-offwhite/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-[calc(1240px_+_2*clamp(16px,3vw,32px))] gap-[10px] px-[clamp(16px,3vw,32px)] pt-[12px] pb-[16px]">
            <div class="flex min-w-[78px] flex-col justify-center">
                @if ($hasTwoAxis)
                    <span data-product-selection-summary class="text-[11px] text-slate-500">{{ $vm->colorOptions[0]['label'] ?? '' }} · {{ $vm->sizeOptions[0]['label'] ?? '' }}</span>
                @elseif ($hasColorOnly)
                    <span data-product-selection-summary class="text-[11px] text-slate-500">{{ $vm->colorOptions[0]['label'] ?? '' }}</span>
                @endif
                <span data-product-sticky-price class="font-display text-[clamp(17px,1.8vw,20px)] font-bold text-brand-navy">{{ $vm->priceLabel }}</span>
            </div>

            @if ($waLink)
                <a data-product-whatsapp href="{{ $waLink }}" target="_blank" rel="noopener" title="Falar com a Cae no WhatsApp" data-ga-event="whatsapp_click" data-ga-payload="{{ json_encode(['location' => 'product_sticky', 'product' => $vm->title]) }}" class="font-display flex flex-1 items-center justify-center rounded-[16px] bg-brand-purple p-[16px] text-[clamp(15.5px,1.5vw,17px)] font-bold text-white shadow-[0_10px_24px_-8px_rgba(150,71,178,0.75)]">Quero esse no zap</a>
            @else
                <x-ui.button :href="route('contact.show')" variant="primary" class="flex-1 text-center text-[15px]">Falar com a gente</x-ui.button>
            @endif
        </div>
    </div>
</x-layout.app>
