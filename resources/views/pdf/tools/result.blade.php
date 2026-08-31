<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $tool->title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; font-size: 13px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .tagline { color: #64748b; margin: 0 0 28px; }
        .field { margin-bottom: 20px; }
        .field-label { font-weight: bold; color: #1e293b; margin-bottom: 4px; }
        .field-value { color: #334155; }
        ul { margin: 4px 0 0 18px; padding: 0; }
        .subitem { margin-top: 8px; padding: 8px 0 8px 12px; border-left: 2px solid #e2e8f0; }
        .subitem-title { font-weight: bold; color: #1e293b; margin-bottom: 3px; }
        .subitem-details { margin: 0; padding: 0; list-style: none; }
        .subitem-details li { margin-top: 2px; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8; }
    </style>
</head>
@php
    $subLabels = [
        'description' => 'Descrição',
        'timeframe' => 'Prazo',
        'investment' => 'Investimento',
    ];
@endphp
<body>
    <h1>{{ $tool->title }}</h1>
    <p class="tagline">Resultado gerado para {{ $submission->name }}</p>

    @foreach ($rows as $row)
        <div class="field">
            <div class="field-label">{{ $row['label'] }}</div>
            <div class="field-value">
                @if (is_array($row['value']))
                    @if (isset($row['value'][0]) && is_array($row['value'][0]))
                        {{-- lista de objetos, ex: etapas de entrega com nome/descrição/prazo/investimento --}}
                        @foreach ($row['value'] as $index => $item)
                            <div class="subitem">
                                <p class="subitem-title">{{ $index + 1 }}. {{ $item['name'] ?? $item['phase'] ?? $item['title'] ?? 'Item '.($index + 1) }}</p>
                                <ul class="subitem-details">
                                    @foreach ($item as $itemKey => $itemValue)
                                        @unless (in_array($itemKey, ['name', 'phase', 'title'], true))
                                            <li><strong>{{ $subLabels[$itemKey] ?? ucfirst(str_replace('_', ' ', (string) $itemKey)) }}:</strong> {{ $itemValue }}</li>
                                        @endunless
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @else
                        <ul>
                            @foreach ($row['value'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    {{ $row['value'] }}
                @endif
            </div>
        </div>
    @endforeach

    <p class="footer">Gerado por {{ config('app.name') }} em {{ $submission->created_at?->format('d/m/Y') }}. Estimativa inicial, sujeita a ajustes após uma análise detalhada.</p>
</body>
</html>
