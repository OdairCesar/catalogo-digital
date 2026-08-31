<x-layout.app title="Estados atendidos — OD Tec" description="Conheça os estados onde a OD Tec já desenvolve sites, sistemas e aplicativos sob medida.">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$breadcrumbs" />

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title as="h1" eyebrow="Estados atendidos" class="mb-10">Onde já estamos presentes</x-ui.section-title>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($states as $state)
                    <x-ui.card>
                        <h3 class="text-lg font-bold">{{ $state->name }}</h3>
                        <p class="text-[15px] leading-relaxed text-slate-500">{{ str($state->intro)->limit(110) }}</p>
                        <a href="{{ route('states.show', $state) }}" title="Ver cidades em {{ $state->name }}" class="text-sm font-bold text-blue-600">Ver cidades em {{ $state->name }} &rarr;</a>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </section>
</x-layout.app>
