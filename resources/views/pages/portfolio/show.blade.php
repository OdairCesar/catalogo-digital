<x-layout.app :title="$vm->seo->title" :description="$vm->seo->description" :canonical="$vm->seo->canonical" :robots="$vm->seo->robots" :og-image="$vm->coverImageUrl">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$vm->jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$vm->breadcrumbs" />

    <article class="px-5 py-16 sm:px-8 lg:px-14 lg:py-20">
        <div class="mx-auto max-w-3xl">
            @if ($vm->serviceName)
                <a href="{{ $vm->serviceUrl }}" title="{{ $vm->serviceName }}" class="text-sm font-bold tracking-wide text-blue-600 uppercase">{{ $vm->serviceName }}</a>
            @endif

            <h1 class="mt-3 mb-4 text-[32px] leading-[1.15] font-extrabold tracking-tight text-balance sm:text-[42px]">
                {{ $vm->title }}
            </h1>

            <p class="mb-10 text-[17px] leading-relaxed text-slate-500">{{ $vm->excerpt }}</p>

            @if ($vm->coverImageUrl)
                <img src="{{ $vm->coverImageUrl }}" alt="{{ $vm->title }}" class="mb-10 aspect-[16/9] w-full rounded-[20px] object-cover">
            @endif

            @if ($vm->content)
                <div class="blog-content">
                    {!! $vm->content !!}
                </div>
            @endif

            @if ($vm->externalUrl)
                <a href="{{ $vm->externalUrl }}" target="_blank" rel="noopener noreferrer" title="Ver projeto no ar"
                    class="mt-10 inline-flex items-center gap-2 text-sm font-bold text-blue-600">
                    Ver projeto no ar &rarr;
                </a>
            @endif
        </div>
    </article>

    @if (! empty($vm->relatedItems))
        <section class="border-t border-slate-800/10 px-5 py-16 sm:px-8 lg:px-14">
            <div class="mx-auto max-w-6xl">
                <x-ui.section-title class="mb-10">Outros projetos</x-ui.section-title>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($vm->relatedItems as $related)
                        <x-ui.post-card
                            :title="$related['title']"
                            :excerpt="$related['excerpt']"
                            :url="$related['url']"
                            :cover-image-url="$related['coverImageUrl']"
                            :category-label="$related['serviceName']"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-section.cta title="Vamos construir seu próximo produto digital?"
        :button="['label' => 'Falar com a OD Tec', 'url' => route('contact.show')]" />
</x-layout.app>
