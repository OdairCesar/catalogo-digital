<x-layout.app :title="$vm->seo->title" :description="$vm->seo->description" :canonical="$vm->seo->canonical" :robots="$vm->seo->robots">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$vm->jsonLd" />
    </x-slot:jsonLd>

    <div
        x-data="{ chatOpen: false }"
        x-effect="document.body.style.overflow = chatOpen ? 'hidden' : ''"
        @keydown.escape.window="chatOpen = false"
    >
        <x-ui.breadcrumb :items="$vm->breadcrumbs" />

        <x-section.hero :eyebrow="'Serviço'" :title="$vm->title" :subtitle="$vm->subtitle"
            :primary="['label' => 'Solicitar orçamento', 'modal' => true]" dark>
            @if ($vm->heroImageUrl)
                <img src="{{ $vm->heroImageUrl }}" alt="{{ $vm->title }}" class="h-full w-full rounded-3xl object-cover" />
            @endif
        </x-section.hero>

        <x-section.problem :title="'Sobre este serviço'">
            <p>{{ $vm->description }}</p>
        </x-section.problem>

        <x-section.benefits eyebrow="Benefícios" :title="'Por que escolher a OD Tec'" :items="$vm->benefits" />

        @if (! empty($vm->faq))
            <x-section.faq eyebrow="Dúvidas frequentes" title="Perguntas frequentes" :items="$vm->faq" />
        @endif

        <x-section.related-links :links="$vm->relatedLinks" />

        <x-section.cta title="Vamos construir seu próximo produto digital?"
            :button="['label' => 'Falar com a OD Tec', 'modal' => true]" />

        <x-ui.consultant-modal />
    </div>
</x-layout.app>
