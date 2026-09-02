<x-layout.app title="Fit By Cae — Looks de verdade para corpos de verdade" description="Moda fitness que valoriza corpos reais: leggings, tops e conjuntos do 36 ao 52, escolhidos e testados pela Cae.">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    @php
        $chooseLookLink = $company?->whatsappLink('Oi Cae! Vim pelo site e quero uma ajuda pra escolher um look 💜');
    @endphp

    <section class="relative h-[clamp(470px,62vw,720px)] overflow-hidden">
        <div data-hero-parallax class="absolute inset-x-0 -inset-y-[7%] bg-gradient-to-br from-brand-purple via-brand-purple-dark to-brand-navy">
            <img src="{{ asset('imgs/icon-fitbycae.png') }}" alt="" aria-hidden="true" class="absolute top-1/2 left-1/2 h-[85%] w-auto -translate-x-1/2 -translate-y-1/2 opacity-20 mix-blend-multiply">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-brand-navy/95 via-brand-navy/25 to-transparent"></div>

        <div class="absolute inset-x-0 bottom-[clamp(20px,3vw,44px)]">
            <div class="mx-auto max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)]">
                @if ($hero?->data['badge'] ?? null)
                    <span class="font-display inline-flex items-center gap-[7px] rounded-[999px] bg-brand-lilac/92 px-[11px] py-[6px] text-[clamp(11px,1.1vw,12px)] font-semibold tracking-wide text-brand-purple-dark uppercase">{{ $hero->data['badge'] }}</span>
                @endif

                <h1 class="font-display mt-[12px] mb-[8px] max-w-[14ch] text-[clamp(34px,5.4vw,64px)] leading-[1.04] font-extrabold tracking-[-0.025em] text-white">{{ $hero?->title ?? 'Looks de verdade para corpos de verdade.' }}</h1>

                <p class="mb-[16px] max-w-[min(100%,460px)] text-[clamp(14.5px,1.5vw,18px)] leading-[1.55] text-white/85">{{ $hero?->content ?? 'Sou a Cae. Eu escolho, visto e testo cada peça antes de chegar em você — pra você se sentir gata do jeito que você é.' }}</p>

                <x-section.extra-fields class="text-white/70" :fields="$hero?->extra_fields ?? []" />

                <div class="flex max-w-[min(100%,420px)] gap-[9px]">
                    @if ($productsEnabled)
                        <a href="{{ route('products.index') }}" title="{{ $hero?->data['cta_label'] ?? 'Ver a coleção' }}" class="font-display flex-1 rounded-[16px] bg-brand-purple px-[18px] py-[16px] text-center text-[clamp(15px,1.4vw,16px)] font-bold text-white transition-[background-color,transform,box-shadow] duration-200 hover:-translate-y-[2px] hover:bg-brand-purple-light hover:shadow-[0_16px_32px_-8px_rgba(150,71,178,0.8)]">{{ $hero?->data['cta_label'] ?? 'Ver a coleção' }}</a>
                    @endif

                    @if ($chooseLookLink)
                        <a href="{{ $chooseLookLink }}" target="_blank" rel="noopener" title="Falar com a Cae no WhatsApp" class="grid h-[56px] w-[56px] flex-none place-items-center rounded-[16px] border border-white/35 bg-white/15 text-white">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.2-.7.8-.8 1-.2.2-.3.2-.5.1-.2-.1-1-.4-2-1.2-.7-.6-1.2-1.4-1.4-1.6-.1-.2 0-.4.1-.5l.4-.4.2-.4c.1-.1 0-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.4c.1.2 1.6 2.5 4 3.5.6.2 1 .4 1.3.5.6.2 1.1.1 1.5.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2-.1-.1-.2-.2-.4-.3Z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @php $trustBarItems = $trustBar?->data['items'] ?? []; @endphp
    @if (!empty($trustBarItems))
        <div class="bg-brand-navy text-center text-white">
            <div class="mx-auto flex max-w-[calc(1240px_+_16px)] px-2 py-[clamp(14px,1.6vw,20px)]">
                @foreach ($trustBarItems as $index => $item)
                    @if ($index > 0)
                        <div class="w-px bg-white/16"></div>
                    @endif
                    <div class="flex-1 px-1.5">
                        <div class="font-display text-[clamp(14px,1.5vw,18px)] font-bold">{{ $item['title'] }}</div>
                        <div class="mt-0.5 text-[clamp(11px,1.1vw,13px)] text-white/62">{{ $item['subtitle'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($productsEnabled && $categories->isNotEmpty())
        @php
            $categoryColors = ['from-brand-purple to-brand-purple-dark', 'from-brand-navy to-slate-700', 'from-brand-magenta to-brand-purple', 'from-brand-purple-dark to-brand-navy'];
        @endphp

        <section class="mx-auto max-w-[calc(1240px_+_2*clamp(18px,3vw,32px))] px-[clamp(18px,3vw,32px)] pt-[clamp(26px,3.4vw,56px)] pb-1.5">
            <h2 class="font-display text-[clamp(21px,2.6vw,32px)] font-bold tracking-[-0.015em] text-brand-navy">Escolhe por aqui</h2>

            <div class="mt-4">
                <x-ui.carousel :options="[
                    'slidesPerView' => 'auto',
                    'spaceBetween' => 16,
                    'breakpoints' => [768 => ['slidesPerView' => 4, 'spaceBetween' => 18]],
                ]">
                    @foreach ($categories as $index => $category)
                        <a
                            href="{{ route('products.category', $category) }}"
                            title="{{ $category->name }}"
                            data-reveal
                            class="swiper-slide group relative h-[clamp(200px,24vw,320px)] w-[300px] overflow-hidden rounded-[clamp(20px,2vw,24px)] bg-gradient-to-br {{ $categoryColors[$index % count($categoryColors)] }}"
                        >
                            @if ($category->imageUrl())
                                <img src="{{ $category->imageUrl() }}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                            @endif
                            <span class="font-display absolute bottom-[clamp(11px,1.2vw,16px)] left-[clamp(12px,1.3vw,18px)] text-[clamp(16px,1.8vw,21px)] font-bold text-white">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </x-ui.carousel>
            </div>
        </section>
    @endif

    @if ($productsEnabled && $products->isNotEmpty())
        <x-section.products title="As mais amadas" description="As peças que as meninas mais pedem de novo — e voltam pra contar." :items="$products" />
    @endif

    @if (!empty($testimonials))
        <x-section.testimonials eyebrow="Quem já vestiu" title="Elas contam melhor<br>do que eu." :items="$testimonials" />
    @endif

    @if ($instagramBlock)
        <x-section.instagram-grid :title="$instagramBlock->title" :description="$instagramBlock->content" :items="$instagramPosts" />
    @endif

    @if ($chooseLookLink && $whatsappBanner)
        <section class="mx-auto max-w-[calc(1240px_+_2*clamp(16px,3vw,32px))] px-[clamp(16px,3vw,32px)] pt-[clamp(30px,3.6vw,56px)]">
            <div data-reveal class="flex flex-wrap items-center gap-[clamp(16px,2vw,32px)] rounded-[clamp(26px,2.6vw,32px)] bg-brand-navy p-[clamp(22px,3vw,44px)]">
                <div class="flex min-w-0 flex-[1_1_260px] items-center gap-3.5">
                    <div class="font-display flex h-[clamp(64px,7vw,96px)] w-[clamp(64px,7vw,96px)] flex-none items-center justify-center rounded-full bg-brand-purple text-2xl font-bold text-white">{{ $whatsappBannerInitial }}</div>
                    <div class="min-w-0">
                        <div class="font-display text-[clamp(17px,2vw,24px)] font-bold text-white">{{ $whatsappBanner->title }}</div>
                        <div class="mt-0.5 text-[clamp(12.5px,1.2vw,14px)] text-white/60">{{ $whatsappBanner->data['subtitle'] ?? '' }}</div>
                    </div>
                </div>
                <p class="min-w-0 flex-[1_1_300px] text-balance text-[clamp(14.5px,1.4vw,16.5px)] leading-[1.6] text-white/86">{{ $whatsappBanner->content }}</p>
                <a href="{{ $chooseLookLink }}" target="_blank" rel="noopener" title="{{ $whatsappBanner->data['button_label'] ?? '' }}" class="font-display flex-[1_1_240px] rounded-[16px] bg-brand-magenta p-[16px] text-center text-[clamp(15px,1.4vw,16px)] font-bold text-white transition-[background-color,transform,box-shadow] duration-200 hover:-translate-y-[2px] hover:bg-[#F06FAB] hover:shadow-[0_14px_28px_-12px_rgba(232,93,158,0.8)]">{{ $whatsappBanner->data['button_label'] ?? '' }}</a>
            </div>
            <x-section.extra-fields class="mx-auto max-w-[calc(1240px_+_2*clamp(16px,3vw,32px))] text-white/70" :fields="$whatsappBanner->extra_fields ?? []" />
        </section>
    @endif
</x-layout.app>
