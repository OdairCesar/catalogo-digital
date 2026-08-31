<x-layout.app title="Cidades atendidas — OD Tec" description="Conheça as cidades onde a OD Tec já desenvolve sites, sistemas e aplicativos sob medida.">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$breadcrumbs" />

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title as="h1" eyebrow="Cidades atendidas" class="mb-10">Onde já estamos presentes</x-ui.section-title>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($cities as $city)
                    <x-ui.card>
                        <h3 class="text-lg font-bold">{{ $city->name }}/{{ $city->state->uf }}</h3>
                        <p class="text-[15px] leading-relaxed text-slate-500">{{ str($city->intro)->limit(110) }}</p>
                        <a href="{{ route('cities.show', $city) }}" title="Ver serviços em {{ $city->name }}" class="text-sm font-bold text-blue-600">Ver serviços em {{ $city->name }} &rarr;</a>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </section>
</x-layout.app>
