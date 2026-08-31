@props([
    'states' => [],
])

@if (count($states) > 0)
    <section class="bg-slate-50 px-5 py-16 sm:px-8 sm:py-[110px] min-[960px]:px-14" x-data="{ activeUf: @js($states[0]->uf) }">
        <div class="mx-auto max-w-[1180px]">
            <x-ui.section-title eyebrow="Cidades atendidas" titleClass="text-[28px] sm:text-[38px]" class="mb-10" data-reveal>Onde já estamos presentes</x-ui.section-title>

            <div class="mb-8 flex flex-wrap gap-3" role="tablist">
                @foreach ($states as $state)
                    <button
                        type="button"
                        role="tab"
                        @click="activeUf = @js($state->uf)"
                        :aria-selected="(activeUf === @js($state->uf)).toString()"
                        class="rounded-full border px-5 py-2.5 text-sm font-semibold transition-colors"
                        :class="activeUf === @js($state->uf) ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-800/10 bg-white text-slate-800 hover:text-blue-600'"
                    >
                        {{ $state->name }}
                    </button>
                @endforeach
            </div>

            @foreach ($states as $state)
                <div x-show="activeUf === @js($state->uf)" role="tabpanel" class="flex flex-wrap gap-3">
                    @foreach ($state->cities as $city)
                        <a href="{{ route('cities.show', $city) }}" title="{{ $city->name }}/{{ $state->uf }}" class="rounded-full border border-slate-800/10 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 hover:text-blue-600">
                            {{ $city->name }}/{{ $state->uf }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
@endif
