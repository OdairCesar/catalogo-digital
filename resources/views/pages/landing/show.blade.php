<x-layout.app :title="$vm->seo->title" :description="$vm->seo->description"
    :canonical="$vm->seo->canonical" :robots="$vm->seo->robots">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$vm->jsonLd" />
    </x-slot:jsonLd>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        <x-ui.breadcrumb :items="$vm->breadcrumbs" />

        <x-section.hero :title="$vm->h1" :subtitle="$vm->subtitle"
            :primary="['label' => $vm->ctaLabel, 'modal' => true]" dark />

        <x-section.problem title="Sobre este serviço">
            <p>{{ $vm->intro }}</p>
        </x-section.problem>

        <x-section.benefits eyebrow="Benefícios" title="Por que escolher a OD Tec" :items="$vm->benefits" />

        @if (! empty($vm->faq))
            <x-section.faq eyebrow="Dúvidas frequentes" title="Perguntas frequentes" :items="$vm->faq" />
        @endif

        <x-section.related-links :links="$vm->relatedLinks" />

        <x-section.cta title="Vamos construir seu próximo produto digital?"
            :button="['label' => $vm->ctaLabel, 'modal' => true]" />

        <x-ui.consultant-modal />
    </div>
</x-layout.app>
