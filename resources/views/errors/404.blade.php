@if (request()->is('admin/*'))
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>404 - Não encontrado</title></head>
<body>
    <h1>404 - Página não encontrada</h1>
</body>
</html>
@else
<x-layout.app title="Página não encontrada" robots="noindex,follow">
    <meta http-equiv="refresh" content="6;url={{ route('home') }}">

    <section class="flex min-h-[60vh] flex-col items-center justify-center px-5 py-24 text-center sm:px-8">
        <p class="text-sm font-bold tracking-wide text-blue-600 uppercase">Erro 404</p>
        <h1 class="mt-3 text-3xl font-bold text-slate-800 sm:text-4xl">Esta página não existe ou não está mais disponível</h1>
        <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-500">
            Você será redirecionado para a página inicial em
            <span id="countdown" class="font-bold text-slate-800">6</span> segundos.
        </p>

        <x-ui.button :href="route('home')" class="mt-8">Ir para a home agora</x-ui.button>
    </section>

    <script>
        (function () {
            var seconds = 6;
            var el = document.getElementById('countdown');
            var timer = setInterval(function () {
                seconds -= 1;
                if (el) {
                    el.textContent = Math.max(seconds, 0);
                }
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = @json(route('home'));
                }
            }, 1000);
        })();
    </script>
</x-layout.app>
@endif
