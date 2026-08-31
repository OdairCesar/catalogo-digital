@props(['result'])

<div class="rounded-2xl border border-blue-600/20 bg-blue-600/5 p-6">
    <h2 class="mb-3 text-lg font-bold text-slate-800">Estimativa de investimento</h2>
    <p class="text-[15px] text-slate-600">
        <strong>Faixa estimada:</strong> {{ $result['estimated_investment_min'] }} a {{ $result['estimated_investment_max'] }}
    </p>
    <p class="mt-1 text-[15px] text-slate-600"><strong>Prazo estimado:</strong> {{ $result['estimated_timeframe'] }}</p>

    <div class="mt-4 border-t border-blue-600/10 pt-4">
        <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-slate-800">
            Complexidade: {{ ucfirst(str_replace('_', ' ', $result['complexity'])) }}
        </span>
    </div>

    <p class="mt-4 text-xs text-slate-500">Esta é uma estimativa inicial, sujeita a ajustes após uma análise técnica detalhada.</p>
</div>

@if (count($result['key_cost_drivers'] ?? []) > 0)
    <div>
        <h2 class="mb-2 text-lg font-bold text-slate-800">Principais fatores de custo</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] leading-relaxed text-slate-600">
            @foreach ($result['key_cost_drivers'] as $driver)
                <li wire:key="driver-{{ $loop->index }}">{{ $driver }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <h2 class="mb-2 text-lg font-bold text-slate-800">Abordagem recomendada</h2>
    <p class="text-[15px] leading-relaxed text-slate-600">{{ $result['recommended_approach'] }}</p>
</div>
