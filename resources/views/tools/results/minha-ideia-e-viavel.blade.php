@props(['result'])

@php
    $verdictLabels = [
        'viavel' => 'Viável',
        'parcialmente_viavel' => 'Parcialmente viável',
        'pouco_viavel' => 'Pouco viável',
    ];
    $verdictColors = [
        'viavel' => 'bg-emerald-100 text-emerald-800',
        'parcialmente_viavel' => 'bg-amber-100 text-amber-800',
        'pouco_viavel' => 'bg-red-100 text-red-800',
    ];
@endphp

<div class="rounded-2xl border border-blue-600/20 bg-blue-600/5 p-6">
    <h2 class="mb-3 text-lg font-bold text-slate-800">Diagnóstico de viabilidade</h2>

    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $verdictColors[$result['viability_verdict']] ?? 'bg-slate-100 text-slate-800' }}">
        {{ $verdictLabels[$result['viability_verdict']] ?? $result['viability_verdict'] }}
    </span>

    <p class="mt-3 text-[15px] text-slate-600"><strong>Nota de viabilidade:</strong> {{ $result['viability_score'] }}/100</p>

    <p class="mt-4 text-xs text-slate-500">Este é um diagnóstico inicial, sujeito a ajustes após uma análise detalhada.</p>
</div>

@if (count($result['strengths'] ?? []) > 0)
    <div>
        <h2 class="mb-2 text-lg font-bold text-slate-800">Pontos fortes</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] leading-relaxed text-slate-600">
            @foreach ($result['strengths'] as $strength)
                <li wire:key="strength-{{ $loop->index }}">{{ $strength }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (count($result['risks'] ?? []) > 0)
    <div>
        <h2 class="mb-2 text-lg font-bold text-slate-800">Riscos</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] leading-relaxed text-slate-600">
            @foreach ($result['risks'] as $risk)
                <li wire:key="risk-{{ $loop->index }}">{{ $risk }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (count($result['recommended_next_steps'] ?? []) > 0)
    <div>
        <h2 class="mb-2 text-lg font-bold text-slate-800">Próximos passos recomendados</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] leading-relaxed text-slate-600">
            @foreach ($result['recommended_next_steps'] as $step)
                <li wire:key="step-{{ $loop->index }}">{{ $step }}</li>
            @endforeach
        </ul>
    </div>
@endif
