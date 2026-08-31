@php
    $title = $productCategory ? "{$productCategory->name} — Coleção Fit By Cae" : 'Coleção — Fit By Cae';
    $description = 'Tecido que segura sem apertar, cós que não enrola e cor que fica linda em qualquer tom de pele. Do 36 ao 52, sempre.';
    $heading = $productCategory ? $productCategory->name : 'Coleção Abraço';
    $countLabel = $items->total().($items->total() === 1 ? ' peça' : ' peças');
@endphp

<x-layout.app :title="$title" :description="$description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <div class="mx-auto max-w-[1240px]">
        <div class="px-[clamp(18px,3vw,32px)] pt-[clamp(20px,2.4vw,36px)]">
            <div class="text-[12.5px] text-[#8A93A3]">Home · Coleção</div>
            <h1 class="font-display mt-[6px] text-[clamp(28px,4vw,46px)] font-extrabold tracking-[-0.025em] text-brand-navy">{{ $heading }}</h1>
            <p class="mt-[8px] max-w-[620px] text-[clamp(14px,1.5vw,16.5px)] leading-[1.55] text-[#6B7688]">{{ $description }}</p>
        </div>

        <div class="mt-[16px] flex flex-wrap gap-[8px] px-[clamp(18px,3vw,32px)] pb-[4px]">
            <a href="{{ route('products.index') }}" title="Todas as categorias" class="font-display flex-none rounded-full border px-[15px] py-[10px] text-[clamp(12.5px,1.2vw,13.5px)] font-semibold {{ $productCategory ? 'border-slate-800/10 bg-white text-slate-600' : 'border-brand-navy bg-brand-navy text-white' }}">Tudo</a>
            @foreach ($categories as $item)
                <a href="{{ route('products.category', $item) }}" title="{{ $item->name }}" class="font-display flex-none rounded-full border px-[15px] py-[10px] text-[clamp(12.5px,1.2vw,13.5px)] font-semibold {{ $productCategory?->is($item) ? 'border-brand-navy bg-brand-navy text-white' : 'border-slate-800/10 bg-white text-slate-600' }}">{{ $item->name }}</a>
            @endforeach
        </div>

        <div class="mt-[14px] flex items-center justify-between gap-[12px] px-[clamp(18px,3vw,32px)]">
            <span class="text-[12.5px] text-[#8A93A3]">{{ $countLabel }}</span>
            <span class="font-display text-[12.5px] font-semibold text-brand-purple">Mais amadas &#9662;</span>
        </div>

        @if ($items->isEmpty())
            <p class="px-[clamp(18px,3vw,32px)] pt-[24px] text-slate-500">Nenhum look publicado por aqui ainda.</p>
        @else
            <div class="mt-[14px] grid grid-cols-[repeat(auto-fill,minmax(min(46%,250px),1fr))] gap-[clamp(14px,1.6vw,22px)] px-[clamp(18px,3vw,32px)] pb-[20px]">
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
        @endif

        @if ($whatsappLink)
            <div class="mx-[clamp(16px,3vw,32px)] mb-[clamp(28px,3.4vw,56px)] flex flex-wrap items-center gap-[16px] rounded-[24px] bg-brand-lilac p-[clamp(20px,2.4vw,36px)]">
                <div class="min-w-0 flex-[1_1_320px]">
                    <div class="font-display text-[clamp(17px,2vw,24px)] font-bold text-[#3D1A4C]">Não sabe o tamanho?</div>
                    <p class="mt-[8px] max-w-[560px] text-[clamp(14px,1.4vw,16px)] leading-[1.6] text-[#5E3A6E]">Me manda cintura e quadril no zap. Eu comparo com o caimento real das peças e te digo qual vestiria melhor em você.</p>
                </div>
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" title="Falar com a Cae no WhatsApp" class="font-display flex-none rounded-[14px] bg-brand-purple px-[22px] py-[15px] text-[clamp(14px,1.4vw,15.5px)] font-bold text-white hover:bg-brand-purple-light">Descobrir meu tamanho</a>
            </div>
        @endif
    </div>

    @if ($items->hasPages())
        <div class="mx-auto max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)] pb-14">
            {{ $items->links() }}
        </div>
    @endif
</x-layout.app>
