@props([
    'title',
    'description' => null,
    'canonical' => null,
    'robots' => 'index,follow',
    'ogImage' => null,
    'ogType' => 'website',
    'ogProduct' => null,
])

<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
    <x-layout.head :title="$title" :description="$description" :canonical="$canonical" :robots="$robots" :og-image="$ogImage" :og-type="$ogType" :og-product="$ogProduct">{{ $jsonLd ?? '' }}</x-layout.head>
<body class="overflow-x-hidden bg-white font-sans text-slate-800 antialiased">
    @if (config('services.google_tag_manager.id') && request()->cookie('cookie_consent') === 'accepted')
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google_tag_manager.id') }}" height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    @endif

    <x-layout.header />

    <main>
        {{ $slot }}
    </main>

    @if (request()->routeIs('home'))
        <x-layout.footer />
    @endif

    <x-layout.screen-switcher />

    <x-layout.cookie-consent />

    @livewireScripts
</body>
</html>
