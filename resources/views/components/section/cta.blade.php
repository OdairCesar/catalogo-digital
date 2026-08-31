@props([
    'title',
    'description' => null,
    'button' => null,
])

<section {{ $attributes->class(['px-5 py-16 text-center sm:px-8 sm:py-[110px] min-[960px]:px-14']) }}>
    <h2 data-reveal class="mb-5 font-display text-[28px] font-extrabold tracking-tight text-balance sm:text-[42px]">{{ $title }}</h2>

    @if ($description)
        <p data-reveal style="transition-delay: 80ms" class="mx-auto mb-9 max-w-[520px] text-[17px] text-slate-500">{{ $description }}</p>
    @endif

    @if ($button)
        <div data-reveal style="transition-delay: 160ms">
            <x-ui.button
                :href="$button['url'] ?? '#'"
                variant="primary"
                class="px-10 py-[18px] text-[17px]"
                gaEvent="cta_click"
                :gaPayload="['location' => 'cta_section', 'label' => $button['label']]"
                :modal="! empty($button['modal'])"
            >{{ $button['label'] }}</x-ui.button>
        </div>
    @endif
</section>
