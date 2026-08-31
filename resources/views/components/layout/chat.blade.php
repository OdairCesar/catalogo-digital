@props([
    'title',
    'description' => null,
    'canonical' => null,
    'robots' => 'index,follow',
    'ogImage' => null,
])

<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
    <x-layout.head :title="$title" :description="$description" :canonical="$canonical" :robots="$robots" :og-image="$ogImage" />
<body class="flex h-full flex-col overflow-hidden bg-slate-50 font-sans text-slate-800 antialiased">
    <header class="flex shrink-0 items-center justify-between border-b border-slate-800/10 bg-white px-5 py-4 sm:px-8">
        <a href="{{ route('home') }}" title="Ir para a página inicial" class="flex items-center">
            <img src="{{ $company?->logoUrl() ?? asset('imgs/logo.png') }}" alt="{{ \App\Models\Company::siteName() }}" class="h-8 w-auto">
        </a>

        <a href="{{ route('home') }}" title="Ver site" class="rounded-full border border-slate-800/20 px-5 py-2.5 text-sm font-semibold text-slate-800 transition-transform duration-200 hover:-translate-y-0.5">
            Ver site
        </a>
    </header>

    <main class="flex min-h-0 flex-1 flex-col">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
