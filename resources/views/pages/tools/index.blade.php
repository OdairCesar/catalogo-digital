<x-layout.app title="Ferramentas gratuitas — OD Tec" description="Calculadoras e diagnósticos gratuitos com IA para te ajudar a decidir sobre o seu próximo projeto ou negócio.">
    <x-ui.breadcrumb :items="$breadcrumbs" />

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <x-ui.section-title as="h1" eyebrow="Ferramentas gratuitas" class="mb-10">Descubra números reais para o seu projeto ou negócio</x-ui.section-title>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($tools as $tool)
                    <x-ui.card>
                        <x-ui.icon-badge :icon="$tool['icon']" bg="bg-blue-600" />
                        <h3 class="text-lg font-bold">{{ $tool['title'] }}</h3>
                        <p class="text-[15px] leading-relaxed text-slate-500">{{ $tool['tagline'] }}</p>
                        <a href="{{ $tool['url'] }}" title="Usar ferramenta: {{ $tool['title'] }}" class="text-sm font-bold text-blue-600">Usar ferramenta &rarr;</a>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </section>

    <x-section.cta title="Precisa de algo mais específico?"
        description="Fale direto com a equipe da OD Tec e conte os detalhes do seu projeto."
        :button="['label' => 'Falar com a OD Tec', 'url' => route('contact.show')]" />
</x-layout.app>
