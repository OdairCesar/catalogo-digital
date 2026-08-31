@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'items' => [],
])

<section {{ $attributes->class(['px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <div class="mx-auto max-w-[1180px]">
        <x-ui.section-title :eyebrow="$eyebrow" :description="$description" titleClass="text-[28px] sm:text-[38px]" class="mb-14" data-reveal>
            {{ $title }}
        </x-ui.section-title>

        <div class="grid gap-6 min-[640px]:grid-cols-2 min-[960px]:grid-cols-3">
            @foreach ($items as $index => $item)
                <x-ui.card :delay="($index * 60).'ms'" :href="$item['url'] ?? null" :title="$item['title']">
                    <x-ui.icon-badge :icon="$item['icon']" :bg="$item['bg'] ?? 'bg-slate-800'" />

                    <h3 class="text-lg font-bold group-hover:text-blue-600">{{ $item['title'] }}</h3>

                    <p class="text-[15px] leading-relaxed text-slate-500">{{ $item['desc'] }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </div>
</section>
