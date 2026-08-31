@props(['result'])

<div class="rounded-2xl border border-blue-600/20 bg-blue-600/5 p-6">
    <h2 class="mb-3 text-lg font-bold text-slate-800">Resumo executivo</h2>
    <p class="text-[15px] leading-relaxed text-slate-600">{{ $result['executive_summary'] }}</p>

    <div class="mt-4 space-y-1 border-t border-blue-600/10 pt-4">
        <p class="text-[15px] text-slate-600"><strong>Prazo estimado:</strong> {{ $result['estimate_timeframe'] }}</p>
        <p class="text-[15px] text-slate-600"><strong>Investimento estimado:</strong> {{ $result['estimate_investment'] }}</p>
    </div>

    <p class="mt-4 text-xs text-slate-500">Esta é uma estimativa inicial, sujeita a ajustes após uma análise técnica detalhada.</p>
</div>

<div>
    <h2 class="mb-2 text-lg font-bold text-slate-800">MVP sugerido</h2>
    <p class="text-[15px] leading-relaxed text-slate-600">{{ $result['mvp'] }}</p>
</div>

@if (count($result['delivery_stages'] ?? []) > 0)
    <div>
        <h2 class="mb-3 text-lg font-bold text-slate-800">Etapas de entrega</h2>
        <div class="space-y-3">
            @foreach ($result['delivery_stages'] as $stage)
                <div wire:key="stage-{{ $loop->index }}" class="rounded-xl border border-slate-800/10 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-800">{{ $loop->iteration }}. {{ $stage['name'] }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $stage['description'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $stage['timeframe'] }} · {{ $stage['investment'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif
