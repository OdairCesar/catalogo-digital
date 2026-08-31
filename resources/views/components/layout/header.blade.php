@php
    $productsEnabled = \App\Enums\SiteModule::Produtos->isEnabled();
    $aboutEnabled = \App\Models\SectionTypeSetting::isEnabled(\App\Enums\SectionType::About);
    $faqEnabled = \App\Models\SectionTypeSetting::isEnabled(\App\Enums\SectionType::FaqGroup);
    $waLink = $company?->whatsappLink('Oi Cae! Vi o site e queria uma ajuda pra escolher meu look 💜');
    $navLinkClass = fn (bool $active): string => $active ? 'text-brand-purple' : 'text-brand-navy hover:text-brand-purple';
@endphp

<header class="sticky top-0 z-20 border-b border-[#EFE7F3] bg-brand-offwhite/92 backdrop-blur-md">
    <div class="mx-auto flex max-w-[calc(1240px_+_2*clamp(16px,3vw,32px))] items-center justify-between gap-[10px] px-[clamp(16px,3vw,32px)] py-[12px]">
        <a href="{{ route('home') }}" title="Ir para a página inicial" class="flex-none">
            <img src="{{ $company?->logoUrl() ?? asset('imgs/logo.png') }}" alt="{{ \App\Models\Company::siteName() }}" class="h-[clamp(30px,3.6vw,40px)] w-auto">
        </a>

        <nav aria-label="Principal" class="hidden min-w-0 flex-1 items-center gap-[clamp(14px,2vw,28px)] overflow-x-auto px-[clamp(8px,2vw,24px)] py-[2px] font-display text-[clamp(13px,1.2vw,14.5px)] font-semibold whitespace-nowrap md:flex">
            @if ($productsEnabled)
                @foreach ($navCategories as $category)
                    <a href="{{ route('products.category', $category) }}" title="{{ $category->name }}" class="{{ $navLinkClass(request()->routeIs('products.category') && request()->route('productCategory')?->is($category)) }}">{{ $category->name }}</a>
                @endforeach
            @endif
            @if ($aboutEnabled)
                <a href="{{ route('about') }}" title="Sobre" class="{{ $navLinkClass(request()->routeIs('about')) }}">Sobre</a>
            @endif
            @if (\App\Enums\SiteModule::Blog->isEnabled())
                <a href="{{ route('blog.index') }}" title="Blog" class="{{ $navLinkClass(request()->routeIs('blog.*')) }}">Blog</a>
            @endif
            @if ($faqEnabled)
                <a href="{{ route('faq.index') }}" title="Perguntas frequentes" class="{{ $navLinkClass(request()->routeIs('faq.index')) }}">Dúvidas</a>
            @endif
        </nav>

        <div class="flex flex-none items-center gap-[8px]">
            <div class="grid h-[36px] w-[36px] flex-none place-items-center rounded-full bg-[#F3E9F7] text-[16px] text-brand-purple" aria-hidden="true">♡</div>
            @if ($waLink)
                <a href="{{ $waLink }}" target="_blank" rel="noopener" title="Falar com a Cae no WhatsApp" data-ga-event="whatsapp_click" data-ga-payload="{{ json_encode(['location' => 'header']) }}" class="font-display flex items-center gap-[6px] rounded-full bg-brand-navy px-[clamp(13px,1.5vw,18px)] py-[10px] text-[clamp(12px,1.2vw,13.5px)] font-semibold whitespace-nowrap text-white">Falar com a Cae</a>
            @endif
        </div>
    </div>
</header>
