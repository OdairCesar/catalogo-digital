@php
    $title = $service ? "{$service->title} — Portfólio OD Tec" : 'Portfólio — OD Tec';
    $description = $service
        ? "Projetos de {$service->title} entregues pela OD Tec."
        : 'Confira projetos de tecnologia entregues pela OD Tec: sites, sistemas, aplicativos e muito mais.';
@endphp

<x-layout.app :title="$title" :description="$description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$breadcrumbs" />

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title as="h1" eyebrow="Portfólio" class="mb-10">
                {{ $service ? $service->title : 'Projetos que já entregamos' }}
            </x-ui.section-title>

            @if ($services->isNotEmpty())
                <div class="mb-10 flex flex-wrap gap-2">
                    <a href="{{ route('portfolio.index') }}" title="Todos"
                        class="rounded-full border px-4 py-1.5 text-sm font-bold {{ $service ? 'border-slate-800/10 text-slate-500 hover:text-blue-600' : 'border-blue-600 bg-blue-600 text-white' }}">
                        Todos
                    </a>
                    @foreach ($services as $item)
                        <a href="{{ route('portfolio.service', $item) }}" title="{{ $item->title }}"
                            class="rounded-full border px-4 py-1.5 text-sm font-bold {{ $service?->is($item) ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-800/10 text-slate-500 hover:text-blue-600' }}">
                            {{ $item->title }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($items->isEmpty())
                <p class="text-slate-500">Nenhum projeto publicado por aqui ainda.</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        <x-ui.post-card
                            :title="$item['title']"
                            :excerpt="$item['excerpt']"
                            :url="$item['url']"
                            :cover-image-url="$item['coverImageUrl']"
                            :category-label="$item['serviceName']"
                        />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layout.app>
