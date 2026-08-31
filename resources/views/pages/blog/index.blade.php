@php
    $title = $category ? "{$category->name} — Blog Fit By Cae" : 'Blog — Fit By Cae';
    $description = $category
        ? ($category->description ?? "Artigos sobre {$category->name} no blog da Fit By Cae.")
        : 'Corpo real, autoestima, styling e bastidores da Fit By Cae.';
@endphp

<x-layout.app :title="$title" :description="$description">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$breadcrumbs" />

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title as="h1" eyebrow="Blog" class="mb-10">
                {{ $category ? $category->name : 'Conteúdo pra você se sentir gata todo dia' }}
            </x-ui.section-title>

            @if ($categories->isNotEmpty())
                <div class="mb-10 flex flex-wrap gap-2">
                    <a href="{{ route('blog.index') }}" title="Todos"
                        class="font-display rounded-full border px-4 py-1.5 text-sm font-bold {{ $category ? 'border-slate-800/10 text-slate-500 hover:text-brand-purple' : 'border-brand-purple bg-brand-purple text-white' }}">
                        Todos
                    </a>
                    @foreach ($categories as $item)
                        <a href="{{ route('blog.category', $item) }}" title="{{ $item->name }}"
                            class="font-display rounded-full border px-4 py-1.5 text-sm font-bold {{ $category?->is($item) ? 'border-brand-purple bg-brand-purple text-white' : 'border-slate-800/10 text-slate-500 hover:text-brand-purple' }}">
                            {{ $item->name }} ({{ $item->posts_count }})
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($posts->isEmpty())
                <p class="text-slate-500">Nenhum post publicado por aqui ainda.</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-ui.post-card
                            :title="$post['title']"
                            :excerpt="$post['excerpt']"
                            :url="$post['url']"
                            :cover-image-url="$post['coverImageUrl']"
                            :category-label="$post['categoryLabel']"
                            :published-at="$post['publishedAt']"
                        />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layout.app>
