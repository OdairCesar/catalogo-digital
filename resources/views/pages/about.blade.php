<x-layout.app title="Sobre a Fit By Cae" description="Moda fitness que valoriza corpos reais — a proposta, os valores e a voz por trás da Fit By Cae.">
    <x-ui.breadcrumb :items="[
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Sobre'],
    ]" />

    @php
        $aboutData = $about?->data ?? [];
        $introParagraphs = $aboutData['intro_paragraphs'] ?? [
            ['paragraph' => 'A proposta central é valorizar o corpo real de cada cliente, com looks que funcionam tanto na academia quanto no dia a dia. A gente se diferencia das marcas "fitness padrão" ao rejeitar o corpo idealizado e inatingível, celebrando corpos reais, com curvas, texturas e tamanhos diversos — do 36 ao 52.'],
            ['paragraph' => 'Roupas fitness que vestem bem, valorizam e fazem você se sentir bonita e confiante — sem padronização de corpo ou photoshop irreal.'],
        ];
        $manifestoParagraphs = array_column($aboutData['manifesto_paragraphs'] ?? [
            ['paragraph' => 'Na Fit By Cae, a gente acredita que corpo real é bonito do jeito que é.'],
            ['paragraph' => 'Sem corpo padrão, sem photoshop, sem cobrança — só looks que vestem bem em quem você é hoje.'],
            ['paragraph' => 'Autoestima também é peça de roupa. A gente escolhe, veste e testa cada uma antes de chegar em você.'],
        ], 'paragraph');
        $manifestoTagline = $aboutData['manifesto_tagline'] ?? 'Looks de verdade para corpos de verdade.';
    @endphp

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-2 lg:items-start">
            <div>
                <x-ui.section-title as="h1" eyebrow="Sobre a Fit By Cae">{{ $about?->title ?? 'A Fit By Cae não vende só peças de treino — vende autoestima' }}</x-ui.section-title>
                @foreach ($introParagraphs as $index => $paragraph)
                    <p class="{{ $index === 0 ? 'mt-5' : 'mt-4' }} text-base leading-relaxed text-slate-500">
                        {{ $paragraph['paragraph'] }}
                    </p>
                @endforeach
            </div>

            <div class="flex flex-col gap-5">
                <div class="rounded-2xl bg-brand-navy p-8 text-white">
                    <div class="font-display mb-3 text-sm font-bold tracking-wide text-brand-lilac uppercase">Missão</div>
                    <p class="text-[15px] leading-relaxed text-white/85">
                        {{ $aboutData['missao_text'] ?? 'Ajudar mulheres a se sentirem fortes e bonitas no próprio corpo, através de moda fitness acessível e autêntica.' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-800/10 bg-slate-50 p-8">
                    <div class="font-display mb-3 text-sm font-bold tracking-wide text-brand-purple uppercase">Quem escolhe</div>
                    <p class="text-[15px] leading-relaxed text-slate-500">
                        {{ $aboutData['quem_escolhe_text'] ?? 'Sou a Cae. Eu escolho, visto e testo cada peça antes de chegar em você — nada entra no catálogo sem passar por mim primeiro.' }}
                    </p>
                </div>
            </div>
        </div>

        <x-section.extra-fields :fields="$about?->extra_fields ?? []" class="mx-auto max-w-6xl" />
    </section>

    <x-section.values eyebrow="Nossos valores" title="O que guia cada peça da Fit By Cae" description="Autenticidade acima da perfeição, representatividade de corpos reais e autoestima como parte do produto — não só a roupa." :items="$aboutData['values'] ?? [
        ['title' => 'Autenticidade', 'desc' => 'Preferimos peças reais, testadas de verdade, a promessas de corpo perfeito.'],
        ['title' => 'Representatividade', 'desc' => 'Corpos com curvas, texturas e tamanhos diversos — do 36 ao 52 em todas as peças.'],
        ['title' => 'Autoestima', 'desc' => 'A gente vende autoestima tanto quanto vende roupa. Uma coisa não vem sem a outra.'],
        ['title' => 'Proximidade', 'desc' => 'Conversa de amiga, não de vitrine. Você fala direto com a Cae, sem letra miúda.'],
    ]" />

    <x-section.manifesto :paragraphs="$manifestoParagraphs" :tagline="$manifestoTagline" />

    @php $ctaLink = $company?->whatsappLink('Oi Cae! Vi a página Sobre e quero saber mais 💜'); @endphp

    <x-section.cta
        :title="$aboutData['cta_title'] ?? 'Ficou com vontade de conhecer os looks?'"
        :description="$aboutData['cta_description'] ?? 'Dá uma olhada na coleção ou fala direto comigo — sem compromisso.'"
        :button="$ctaLink ? ['label' => $aboutData['cta_button_label'] ?? 'Falar com a Cae', 'url' => $ctaLink] : ['label' => 'Falar com a gente', 'url' => route('contact.show')]" />
</x-layout.app>
