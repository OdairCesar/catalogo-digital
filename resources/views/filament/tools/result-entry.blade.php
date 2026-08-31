{{--
    O Filament passa este HTML pelo sanitizador do Symfony antes de exibir, que
    remove a tag <style> por segurança (mas permite o atributo style="" inline
    em qualquer elemento). Por isso todo estilo aqui é inline, não em classes.
--}}
@php
    $subLabels = [
        'description' => 'Descrição',
        'timeframe' => 'Prazo',
        'investment' => 'Investimento',
    ];
@endphp
<div>
    @forelse ($rows as $row)
        <div style="margin-bottom: 16px;">
            <div style="font-weight: 600; color: #0f172a; margin-bottom: 4px;">{{ $row['label'] }}</div>
            <div style="color: #475569; line-height: 1.5;">
                @if (is_array($row['value']))
                    @if (isset($row['value'][0]) && is_array($row['value'][0]))
                        {{-- lista de objetos, ex: etapas com nome/descrição/prazo/investimento --}}
                        @foreach ($row['value'] as $index => $item)
                            <div style="margin-top: 8px; padding: 8px 0 8px 12px; border-left: 2px solid #e2e8f0;">
                                <p style="font-weight: 600; color: #0f172a; margin: 0 0 3px;">{{ $index + 1 }}. {{ $item['name'] ?? $item['phase'] ?? $item['title'] ?? 'Item '.($index + 1) }}</p>
                                <ul style="list-style: none; margin: 0; padding: 0;">
                                    @foreach ($item as $itemKey => $itemValue)
                                        @unless (in_array($itemKey, ['name', 'phase', 'title'], true))
                                            <li style="margin-top: 2px;"><strong>{{ $subLabels[$itemKey] ?? ucfirst(str_replace('_', ' ', (string) $itemKey)) }}:</strong> {{ $itemValue }}</li>
                                        @endunless
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @else
                        <ul style="margin: 4px 0 0 20px; padding: 0;">
                            @foreach ($row['value'] as $item)
                                <li style="margin-top: 2px;">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <p style="margin: 0;">{{ $row['value'] }}</p>
                @endif
            </div>
        </div>
    @empty
        <p style="color: #94a3b8; margin: 0;">Nenhum resultado gerado ainda.</p>
    @endforelse
</div>
