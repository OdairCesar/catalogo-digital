@props(['result'])

<div class="rounded-2xl border border-blue-600/20 bg-blue-600/5 p-6">
    <h2 class="mb-3 text-lg font-bold text-slate-800">Faturamento potencial</h2>
    <p class="text-[15px] text-slate-600">
        <strong>Faixa estimada:</strong> {{ $result['estimated_monthly_revenue_min'] }} a {{ $result['estimated_monthly_revenue_max'] }} por mês
    </p>

    <div class="mt-4 space-y-1 border-t border-blue-600/10 pt-4">
        <p class="text-[15px] text-slate-600"><strong>Custo estimado do MVP:</strong> {{ $result['mvp_cost_estimate'] }}</p>
        <p class="text-[15px] text-slate-600"><strong>Prazo estimado do MVP:</strong> {{ $result['mvp_timeframe'] }}</p>
    </div>

    <p class="mt-4 text-xs text-slate-500">Esta é uma estimativa inicial, sujeita a ajustes após uma análise técnica detalhada.</p>
</div>

@if (count($result['recommended_features'] ?? []) > 0)
    <div>
        <h2 class="mb-2 text-lg font-bold text-slate-800">Funcionalidades recomendadas para o MVP</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] leading-relaxed text-slate-600">
            @foreach ($result['recommended_features'] as $feature)
                <li wire:key="feature-{{ $loop->index }}">{{ $feature }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (count($result['roadmap'] ?? []) > 0)
    <div>
        <h2 class="mb-3 text-lg font-bold text-slate-800">Roteiro sugerido</h2>
        <div class="space-y-3">
            @foreach ($result['roadmap'] as $phase)
                <div wire:key="phase-{{ $loop->index }}" class="rounded-xl border border-slate-800/10 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-800">{{ $loop->iteration }}. {{ $phase['phase'] }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $phase['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div>
    <h2 class="mb-2 text-lg font-bold text-slate-800">Análise de viabilidade</h2>
    <p class="text-[15px] leading-relaxed text-slate-600">{{ $result['viability_notes'] }}</p>
</div>
