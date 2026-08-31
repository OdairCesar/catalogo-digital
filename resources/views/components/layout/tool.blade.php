@props([
    'title',
    'description' => null,
    'canonical' => null,
    'robots' => 'index,follow',
    'ogImage' => null,
])

<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
    <x-layout.head :title="$title" :description="$description" :canonical="$canonical" :robots="$robots" :og-image="$ogImage">{{ $jsonLd ?? '' }}</x-layout.head>
<body class="bg-white font-sans text-slate-800 antialiased">
    <main>
        {{ $slot }}
    </main>

    <x-layout.cookie-consent />

    @livewireScripts
</body>
</html>
