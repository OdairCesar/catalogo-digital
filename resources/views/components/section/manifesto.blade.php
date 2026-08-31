@props([
    'paragraphs' => [
        'Na Fit By Cae, a gente acredita que corpo real é bonito do jeito que é.',
        'Sem corpo padrão, sem photoshop, sem cobrança — só looks que vestem bem em quem você é hoje.',
        'Autoestima também é peça de roupa. A gente escolhe, veste e testa cada uma antes de chegar em você.',
    ],
    'tagline' => 'Looks de verdade para corpos de verdade.',
])

<section class="bg-brand-navy px-5 py-16 text-center text-white sm:px-8 sm:py-[120px] min-[960px]:px-14">
    <div data-reveal class="mx-auto max-w-[760px]">
        @foreach ($paragraphs as $index => $paragraph)
            @if ($index === 0)
                <p class="font-display mb-7 text-[22px] leading-tight font-bold tracking-tight text-balance sm:text-[30px]">
                    {{ $paragraph }}
                </p>
            @else
                <p class="text-[17px] leading-relaxed text-white/65 {{ $loop->last ? 'mb-10' : 'mb-3' }}">
                    {{ $paragraph }}
                </p>
            @endif
        @endforeach

        @if ($tagline)
            <div class="animate-gradient-shift font-display inline-block bg-[length:200%_auto] bg-gradient-to-r from-brand-purple via-brand-magenta to-brand-purple bg-clip-text text-base font-extrabold text-transparent">
                {{ $tagline }}
            </div>
        @endif
    </div>
</section>
