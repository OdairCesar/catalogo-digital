@props([
    'eyebrow' => null,
    'title',
    'items' => [],
    'extraFields' => [],
])

<section {{ $attributes->class(['mx-auto max-w-3xl px-5 py-20 sm:px-8 lg:px-14 lg:py-28']) }}>
    <x-ui.section-title :eyebrow="$eyebrow" class="mb-10" data-reveal>
        {{ $title }}
    </x-ui.section-title>

    <div class="flex flex-col gap-3">
        @foreach ($items as $item)
            <details class="group rounded-2xl border border-slate-800/10 bg-white open:shadow-sm" data-ga-event="faq_open" data-ga-payload="{{ json_encode(['question' => $item['question']]) }}">
                <summary class="font-display flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 font-semibold text-slate-800 marker:content-none">
                    {{ $item['question'] }}
                    <svg class="h-5 w-5 flex-none text-slate-400 transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-[15px] leading-relaxed text-slate-500">
                    {{ $item['answer'] }}
                </div>
            </details>
        @endforeach
    </div>

    <x-section.extra-fields :fields="$extraFields" />
</section>
