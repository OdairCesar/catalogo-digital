<x-layout.app title="Fale com a Fit By Cae" description="Me conta o que você está procurando — a resposta é rápida e sem compromisso.">
    <x-ui.breadcrumb :items="[
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Contato'],
    ]" />

    <section class="px-5 py-20 sm:px-8 lg:px-14 lg:py-28">
        <div class="mx-auto max-w-xl">
            <x-ui.section-title as="h1" eyebrow="Contato" class="mb-4">Fala comigo</x-ui.section-title>

            @if ($whatsappLink)
                <p class="mb-10 text-base leading-relaxed text-slate-500">
                    Pra uma resposta mais rápida, chama no
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="font-bold text-brand-purple" data-ga-event="whatsapp_click">WhatsApp</a>.
                    Se preferir, deixa sua mensagem por aqui que eu retorno assim que possível.
                </p>
            @else
                <p class="mb-10 text-base leading-relaxed text-slate-500">Deixa sua mensagem por aqui que eu retorno assim que possível.</p>
            @endif

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-semibold text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>
                    if (typeof gtag === 'function') {
                        gtag('event', 'generate_lead', { lead_source: 'contact' });
                    }
                </script>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="flex flex-col gap-5" data-contact-form>
                @csrf

                <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                    <label for="website">Deixe este campo em branco</label>
                    <input id="website" name="website" type="text" value="{{ old('website') }}"
                        tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-800">Nome</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-slate-800/15 px-4 py-3 text-[15px]">
                    @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-800">E-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-800/15 px-4 py-3 text-[15px]">
                    @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-800">Telefone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required
                        class="w-full rounded-xl border border-slate-800/15 px-4 py-3 text-[15px]">
                    @error('phone') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="message" class="mb-1.5 block text-sm font-semibold text-slate-800">Mensagem</label>
                    <textarea id="message" name="message" rows="5"
                        class="w-full rounded-xl border border-slate-800/15 px-4 py-3 text-[15px]">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="font-display inline-block rounded-full bg-brand-purple px-8 py-4 text-center text-base font-bold text-white transition-colors hover:bg-brand-purple-light disabled:cursor-not-allowed disabled:opacity-60">
                    Enviar mensagem
                </button>
            </form>
        </div>
    </section>
</x-layout.app>
