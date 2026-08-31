@php
    $isHome = request()->routeIs('home');
    $isList = request()->routeIs('products.index', 'products.category');
    $isProduct = request()->routeIs('products.show');
    $pillClass = fn (bool $active): string => 'inline-block border-0 cursor-pointer rounded-full px-[13px] py-[8px] font-display text-[12px] font-semibold no-underline '.($active ? 'bg-brand-purple text-white' : 'bg-transparent text-white/62 hover:text-white');
    $productPillUrl = $isProduct ? url()->current() : $latestProductUrl;
@endphp

@if (\App\Enums\SiteModule::Produtos->isEnabled() && ($isHome || $isList || $isProduct) && $latestProductUrl)
    @if ($isProduct)
    <div class="fixed right-[14px] bottom-[76px] z-[60] flex gap-[5px] rounded-full bg-brand-navy/92 p-[5px] shadow-[0_10px_26px_-8px_rgba(28,40,57,0.6)] backdrop-blur-[10px]">
        <a href="{{ route('home') }}" title="Home" class="{{ $pillClass($isHome) }}">Home</a>
        <a href="{{ route('products.index') }}" title="Categoria" class="{{ $pillClass($isList) }}">Categorias</a>
    </div>
    @else
    <div class="fixed right-[14px] bottom-5 z-[60] flex gap-[5px] rounded-full bg-brand-navy/92 p-[5px] shadow-[0_10px_26px_-8px_rgba(28,40,57,0.6)] backdrop-blur-[10px]">
        <a href="{{ route('home') }}" title="Home" class="{{ $pillClass($isHome) }}">Home</a>
        <a href="{{ route('products.index') }}" title="Categoria" class="{{ $pillClass($isList) }}">Categorias</a>
    </div>
    @endif
@endif