@if (config('services.google_tag_manager.id'))
    <div
        data-cookie-consent
        class="fixed inset-x-0 bottom-0 z-50 hidden border-t border-slate-800/10 bg-white/95 px-5 py-4 backdrop-blur-md sm:px-8"
    >
        <div class="mx-auto flex max-w-[1180px] flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600">
                Usamos cookies de análise para entender como você usa o site e melhorar sua experiência. Eles só são ativados com a sua permissão.
            </p>
            <div class="flex flex-none gap-3">
                <button type="button" data-cookie-reject class="rounded-full border border-slate-800/20 px-5 py-2.5 text-sm font-semibold text-slate-800">
                    Recusar
                </button>
                <button type="button" data-cookie-accept class="font-display rounded-full bg-brand-purple px-5 py-2.5 text-sm font-semibold text-white">
                    Aceitar
                </button>
            </div>
        </div>
    </div>
@endif
