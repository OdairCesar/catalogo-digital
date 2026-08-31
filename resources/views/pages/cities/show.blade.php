<x-layout.app :title="$vm->seo->title" :description="$vm->seo->description" :canonical="$vm->seo->canonical" :robots="$vm->seo->robots">
    <x-slot:jsonLd>
        <x-seo.json-ld :data="$vm->jsonLd" />
    </x-slot:jsonLd>

    <x-ui.breadcrumb :items="$vm->breadcrumbs" />

    <x-section.hero :eyebrow="$vm->region" :title="'Tecnologia sob medida em '.$vm->name" :subtitle="$vm->intro"
        :primary="['label' => 'Solicitar orçamento', 'url' => route('contact.show')]" dark />

    <x-section.problem :title="'Sobre '.$vm->name">
        <p>{{ $vm->businessText }}</p>
    </x-section.problem>

    <section class="bg-slate-50 px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title eyebrow="Serviços disponíveis" class="mb-10">O que fazemos em {{ $vm->name }}</x-ui.section-title>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($vm->landingPages as $landingPage)
                    <x-ui.card>
                        <x-ui.icon-badge :icon="$landingPage['icon']" bg="bg-blue-600" />
                        <h3 class="text-lg font-bold">{{ $landingPage['label'] }}</h3>
                        <p class="text-[15px] leading-relaxed text-slate-500">{{ $landingPage['subtitle'] }}</p>
                        <a href="{{ $landingPage['url'] }}" title="Saiba mais sobre {{ $landingPage['label'] }}" class="text-sm font-bold text-blue-600">Saiba mais &rarr;</a>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </section>

    <x-section.cta title="Vamos construir seu próximo produto digital?"
        :button="['label' => 'Falar com a OD Tec', 'url' => route('contact.show')]" />
</x-layout.app>
