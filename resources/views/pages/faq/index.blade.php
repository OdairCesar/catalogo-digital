<x-layout.app title="Perguntas frequentes — Fit By Cae" description="Tire suas dúvidas sobre tamanhos, trocas, frete e formas de pagamento na Fit By Cae.">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$breadcrumbs" />

    <section class="px-5 pt-20 sm:px-8 lg:px-14 lg:pt-28">
        <div class="mx-auto max-w-3xl">
            <x-ui.section-title as="h1" eyebrow="Dúvidas frequentes">Perguntas frequentes</x-ui.section-title>
        </div>
    </section>

    @foreach ($groups as $group)
        <x-section.faq :eyebrow="$group['title']" :title="$group['title']" :items="$group['faq']" :extra-fields="$group['extra_fields']" />
    @endforeach

    <x-section.cta title="Ainda tem alguma dúvida?"
        description="Fala comigo — a resposta é rápida e sem compromisso."
        :button="$whatsappLink ? ['label' => 'Falar com a Cae', 'url' => $whatsappLink] : ['label' => 'Falar com a gente', 'url' => route('contact.show')]" />
</x-layout.app>
