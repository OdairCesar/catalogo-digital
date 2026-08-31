@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'primary' => null,
    'secondary' => null,
    'dark' => false,
])

<section {{ $attributes->class(['relative overflow-hidden px-5 py-16 sm:px-8 sm:pt-[100px] sm:pb-[120px] min-[960px]:px-14', $dark ? 'bg-brand-navy text-white' : 'bg-gradient-to-b from-brand-lilac to-brand-offwhite text-brand-navy']) }}>
    <div class="animate-float pointer-events-none absolute -top-[140px] -right-[140px] h-[420px] w-[420px] rounded-full {{ $dark ? 'bg-brand-purple/40' : 'bg-brand-purple/[0.18]' }} blur-3xl" aria-hidden="true"></div>

    @if ($dark)
        <div class="animate-float-reverse pointer-events-none absolute right-[120px] -bottom-[160px] h-[360px] w-[360px] rounded-full bg-brand-magenta/[0.28] blur-3xl" aria-hidden="true"></div>
    @endif

    <div class="relative mx-auto grid max-w-[1180px] items-center gap-10 min-[960px]:grid-cols-[1.1fr_0.9fr] min-[960px]:gap-16">
        <div data-reveal>
            @if ($eyebrow)
                <div class="mb-7 inline-flex items-center gap-2 rounded-full border px-4 py-[7px] font-display text-[13px] font-bold {{ $dark ? 'border-white/25 bg-white/10 text-white' : 'border-brand-purple/20 bg-white/70 text-brand-purple-dark' }}">
                    {{ $eyebrow }}
                </div>
            @endif

            <h1 class="mb-6 font-display text-[38px] leading-[1.08] font-extrabold tracking-tight text-balance sm:text-[54px]">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="mb-9 max-w-xl text-lg leading-relaxed {{ $dark ? 'text-white/75' : 'text-brand-navy/70' }}">
                    {{ $subtitle }}
                </p>
            @endif

            @if ($primary || $secondary)
                <div class="hero-ctas flex flex-wrap gap-4 max-[480px]:flex-col">
                    @if ($primary)
                        <x-ui.button
                            :href="$primary['url'] ?? '#'"
                            variant="primary"
                            class="max-[480px]:w-full max-[480px]:box-border max-[480px]:text-center"
                            gaEvent="cta_click"
                            :gaPayload="['location' => 'hero', 'label' => $primary['label']]"
                            :modal="! empty($primary['modal'])"
                        >{{ $primary['label'] }}</x-ui.button>
                    @endif

                    @if ($secondary)
                        <x-ui.button
                            :href="$secondary['url'] ?? '#'"
                            :variant="$dark ? 'outline-dark' : 'outline-light'"
                            class="max-[480px]:w-full max-[480px]:box-border max-[480px]:text-center"
                            gaEvent="cta_click"
                            :gaPayload="['location' => 'hero', 'label' => $secondary['label']]"
                            :modal="! empty($secondary['modal'])"
                        >{{ $secondary['label'] }}</x-ui.button>
                    @endif
                </div>
            @endif
        </div>

        @if ($slot->isNotEmpty())
            <div data-reveal class="relative aspect-[4/3] w-full sm:aspect-[3/2]">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
