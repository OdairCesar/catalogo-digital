<x-layout.app :title="$vm->seo->title" :description="$vm->seo->description" :canonical="$vm->seo->canonical" :robots="$vm->seo->robots" :og-image="$vm->coverImageUrl">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$vm->jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$vm->breadcrumbs" />

    <article class="px-5 py-16 sm:px-8 lg:px-14 lg:py-20">
        <div class="mx-auto max-w-3xl">
            @if ($vm->categoryLabel)
                <a href="{{ $vm->categoryUrl }}" title="{{ $vm->categoryLabel }}" class="font-display text-sm font-bold tracking-wide text-brand-purple uppercase">{{ $vm->categoryLabel }}</a>
            @endif

            <h1 class="mt-3 mb-4 text-[32px] leading-[1.15] font-extrabold tracking-tight text-balance sm:text-[42px]">
                {{ $vm->title }}
            </h1>

            <div class="mb-10 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                @if ($vm->authorName)
                    <span>{{ $vm->authorName }}</span>
                    <span aria-hidden="true">&middot;</span>
                @endif
                @if ($vm->publishedAt)
                    <time datetime="{{ $vm->publishedAt->toAtomString() }}">{{ $vm->publishedAt->translatedFormat('d \d\e F \d\e Y') }}</time>
                @endif
            </div>

            @if ($vm->coverImageUrl)
                <img src="{{ $vm->coverImageUrl }}" alt="{{ $vm->title }}" class="mb-10 aspect-[16/9] w-full rounded-[20px] object-cover">
            @endif

            <div class="blog-content">
                {!! $vm->contentHtml !!}
            </div>

            @if (! empty($vm->tags))
                <div class="mt-10 flex flex-wrap gap-2">
                    @foreach ($vm->tags as $tag)
                        <span class="rounded-full border border-slate-800/10 px-3 py-1 text-xs font-bold text-slate-500">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </article>

    @if (! empty($vm->relatedPosts))
        <section class="border-t border-slate-800/10 px-5 py-16 sm:px-8 lg:px-14">
            <div class="mx-auto max-w-6xl">
                <x-ui.section-title class="mb-10">Continue lendo</x-ui.section-title>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($vm->relatedPosts as $related)
                        <x-ui.post-card
                            :title="$related['title']"
                            :excerpt="$related['excerpt']"
                            :url="$related['url']"
                            :cover-image-url="$related['coverImageUrl']"
                            :category-label="$related['categoryLabel']"
                            :published-at="$related['publishedAt']"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-section.cta title="Quer ver os looks da coleção?"
        description="Dá uma olhada no catálogo ou fala direto comigo."
        :button="$whatsappLink ? ['label' => 'Falar com a Cae', 'url' => $whatsappLink] : ['label' => 'Falar com a gente', 'url' => route('contact.show')]" />
</x-layout.app>
