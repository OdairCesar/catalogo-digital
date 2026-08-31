{{--
    Mesma regra do parcial de resultado: o sanitizador do Filament remove
    tags <style>, então todo estilo aqui é inline. As bolhas seguem a mesma
    lógica visual do chat no site (IA à esquerda, visitante à direita).
--}}
<div>
    @forelse ($messages as $message)
        @php $isVisitor = ($message['role'] ?? null) === 'user'; @endphp
        <div style="display: flex; justify-content: {{ $isVisitor ? 'flex-end' : 'flex-start' }}; margin-bottom: 8px;">
            <div style="max-width: 75%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.5; {{ $isVisitor ? 'background: #2563eb; color: #ffffff;' : 'background: #f1f5f9; color: #0f172a; border: 1px solid #e2e8f0;' }}">
                <div style="font-size: 10px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 3px; opacity: 0.7;">
                    {{ $isVisitor ? 'Visitante' : 'IA' }}
                </div>
                {{ $message['content'] ?? '' }}
            </div>
        </div>
    @empty
        <p style="color: #94a3b8; margin: 0;">Nenhuma mensagem ainda.</p>
    @endforelse
</div>
