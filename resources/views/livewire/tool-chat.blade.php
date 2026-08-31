<div class="flex h-full min-h-0 flex-1 flex-col">
    @if ($step === 'chat')
        <div class="flex min-h-0 flex-1 flex-col gap-4">
            <div
                x-data
                x-init="
                    $el.scrollTop = $el.scrollHeight;
                    new MutationObserver(() => { $el.scrollTop = $el.scrollHeight }).observe($el, { childList: true, subtree: true });
                "
                class="flex min-h-0 flex-1 flex-col gap-3 overflow-y-auto rounded-2xl border border-slate-800/10 bg-white p-5"
            >
                @foreach ($chatLog as $index => $entry)
                    <div wire:key="log-{{ $index }}" class="flex {{ $entry['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-2xl px-4 py-3 text-[15px] leading-relaxed {{ $entry['role'] === 'user' ? 'bg-blue-600 text-white' : 'border border-slate-800/10 bg-white text-slate-800' }}">
                            {{ $entry['content'] }}
                        </div>
                    </div>
                @endforeach

                @if ($submission)
                    @foreach ($submission->messages as $index => $entry)
                        <div wire:key="ai-{{ $index }}" class="flex {{ $entry['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-2xl px-4 py-3 text-[15px] leading-relaxed {{ $entry['role'] === 'user' ? 'bg-blue-600 text-white' : 'border border-slate-800/10 bg-white text-slate-800' }}">
                                {{ $entry['content'] }}
                            </div>
                        </div>
                    @endforeach
                @endif

                <div wire:loading wire:target="submitInput" class="flex justify-start">
                    <div class="rounded-2xl border border-slate-800/10 bg-white px-4 py-3 text-[15px] text-slate-400">
                        digitando...
                    </div>
                </div>
            </div>

            <form wire:submit="submitInput" class="flex gap-3">
                <input
                    wire:model="input"
                    wire:loading.attr="readonly"
                    wire:target="submitInput"
                    type="{{ $stage === 'email' ? 'email' : ($stage === 'phone' ? 'tel' : 'text') }}"
                    placeholder="Digite sua resposta..."
                    class="w-full rounded-xl border border-slate-800/15 px-4 py-3 text-[15px]"
                >
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submitInput"
                    class="inline-block shrink-0 rounded-full bg-gradient-to-br from-blue-600 via-emerald-500 to-blue-600 px-6 py-3 text-center text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Enviar
                </button>
            </form>

            @error('input') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    @elseif ($step === 'generating')
        <div wire:poll.3s="pollReportStatus" class="flex flex-1 flex-col items-center justify-center rounded-2xl border border-slate-800/10 bg-white px-6 py-16 text-center">
            <p class="text-lg font-semibold text-slate-800">Estamos preparando seu resultado...</p>
            <p class="mt-2 text-[15px] text-slate-500">Isso leva menos de um minuto. Não feche esta página.</p>
        </div>
    @elseif ($step === 'result')
        <div class="flex min-h-0 flex-1 flex-col gap-8 overflow-y-auto py-2">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Seu resultado está pronto!</h2>
                <p class="mt-1 text-[15px] text-slate-500">{{ $tool->tagline }}</p>
            </div>

            @include($tool->resultView, ['result' => $submission->result])

            <div class="flex flex-wrap gap-3">
                @if ($pdfUrl)
                    <x-ui.button :href="$pdfUrl" variant="primary" :new-tab="true">Baixar PDF</x-ui.button>
                @endif
            </div>
        </div>
    @elseif ($step === 'failed')
        <div class="flex flex-1 flex-col items-center justify-center rounded-2xl border border-red-600/20 bg-red-600/5 px-6 py-10 text-center">
            <p class="text-lg font-semibold text-slate-800">Não conseguimos gerar seu resultado agora.</p>
            <p class="mt-2 text-[15px] text-slate-600">Por favor, fale diretamente com nossa equipe que damos continuidade manualmente.</p>
            <x-ui.button :href="route('contact.show')" variant="primary" class="mt-6">Falar com a equipe</x-ui.button>
        </div>
    @endif
</div>
