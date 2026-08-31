@use('Illuminate\Support\Facades\Vite')

@props([
    'title',
    'description' => null,
    'canonical' => null,
    'robots' => 'index,follow',
    'ogImage' => null,
    'ogType' => 'website',
    'ogProduct' => null,
])

<head>
    <script>document.documentElement.classList.add('js')</script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }}</title>

    @if ($company?->faviconUrl())
        <link rel="icon" href="{{ $company->faviconUrl() }}">
        <link rel="apple-touch-icon" href="{{ $company->faviconUrl() }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('imgs/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('imgs/favicon-32x32.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('imgs/apple-touch-icon.png') }}">
    @endif
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#9647B2">

    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

    <meta name="robots" content="{{ $robots }}">

    @php
        $page = (int) request()->query('page', 1);
        $resolvedCanonical = $canonical ?? url()->current().($page > 1 ? '?page='.$page : '');
        $resolvedOgImage = $ogImage ?? asset('imgs/og-image.png');
    @endphp

    <link rel="canonical" href="{{ $resolvedCanonical }}">

    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ \App\Models\Company::siteName() }}">
    <meta property="og:title" content="{{ $title }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:url" content="{{ $resolvedCanonical }}">
    <meta property="og:image" content="{{ $resolvedOgImage }}">
    @unless ($ogImage)
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endunless
    <meta property="og:image:alt" content="{{ $title }}">
    <meta property="og:locale" content="pt_BR">

    @if ($ogType === 'product' && $ogProduct)
        @if (! empty($ogProduct['price']))
            <meta property="product:price:amount" content="{{ number_format($ogProduct['price'], 2, '.', '') }}">
        @endif
        @if (! empty($ogProduct['currency']))
            <meta property="product:price:currency" content="{{ $ogProduct['currency'] }}">
        @endif
        @if (! empty($ogProduct['availability']))
            <meta property="product:availability" content="{{ $ogProduct['availability'] }}">
        @endif
        @if (! empty($ogProduct['brand']))
            <meta property="product:brand" content="{{ $ogProduct['brand'] }}">
        @endif
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    @if ($description)
        <meta name="twitter:description" content="{{ $description }}">
    @endif
    <meta name="twitter:image" content="{{ $resolvedOgImage }}">

    {{ $slot ?? '' }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ Vite::fonts() }}
    @livewireStyles

    @if (config('services.google_tag_manager.id'))
        <script>
            window.GTM_CONTAINER_ID = "{{ config('services.google_tag_manager.id') }}";
            window.dataLayer = window.dataLayer || [];

            if (window.localStorage.getItem('cookie_consent') === 'accepted') {
                window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                var gtmScript = document.createElement('script');
                gtmScript.async = true;
                gtmScript.src = 'https://www.googletagmanager.com/gtm.js?id=' + window.GTM_CONTAINER_ID;
                document.head.appendChild(gtmScript);
            }
        </script>
    @endif
</head>
