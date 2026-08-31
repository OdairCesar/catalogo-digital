@props(['result'])

<div class="rounded-2xl border border-blue-600/20 bg-blue-600/5 p-6">
    <h2 class="mb-3 text-lg font-bold text-slate-800">Desperdício estimado</h2>
    <p class="text-[15px] text-slate-600"><strong>Por mês:</strong> {{ $result['estimated_monthly_waste'] }}</p>
    <p class="mt-1 text-[15px] text-slate-600"><strong>Por ano:</strong> {{ $result['estimated_annual_waste'] }}</p>

    <div class="mt-4 border-t border-blue-600/10 pt-4">
        <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-slate-800">
            Economia potencial com automação: {{ $result['automation_savings_percent'] }}
        </span>
    </div>

    <p class="mt-4 text-xs text-slate-500">Esta é uma estimativa inicial, sujeita a ajustes após uma análise detalhada do processo.</p>
</div>

<div>
    <h2 class="mb-2 text-lg font-bold text-slate-800">Recomendação de automação</h2>
    <p class="text-[15px] leading-relaxed text-slate-600">{{ $result['automation_recommendation'] }}</p>
</div>

@if (count($result['risk_factors'] ?? []) > 0)
    <div>
        <h2 class="mb-2 text-lg font-bold text-slate-800">Fatores de risco do processo atual</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] leading-relaxed text-slate-600">
            @foreach ($result['risk_factors'] as $risk)
                <li wire:key="risk-{{ $loop->index }}">{{ $risk }}</li>
            @endforeach
        </ul>
    </div>
@endif
