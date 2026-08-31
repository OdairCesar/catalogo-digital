<?php

test('the tools hub page resolves', function () {
    $this->get(route('tools.index'))->assertOk();
});

test('each tool renders its bespoke landing page', function (string $slug, string $headline) {
    $this->get(route('tools.show', $slug))
        ->assertOk()
        ->assertSee($headline);
})->with([
    ['quanto-vale-minha-ideia', 'Toda ideia de negócio esconde um número atrás dela.'],
    ['quanto-custa-meu-sistema', 'Todo sistema começa com uma planta. E um orçamento.'],
    ['quanto-desperdico-com-processos-manuais', 'Sua empresa está vazando dinheiro. Quanto por mês?'],
    ['minha-ideia-e-viavel', 'Sua ideia tem pulso? Vamos verificar.'],
    ['consultor-ia', 'Consultoria de Projeto com IA'],
]);

test('an unknown tool slug 404s', function () {
    $this->get(route('tools.show', 'nao-existe'))->assertNotFound();
});

test('the old consultor-ia url permanently redirects to the new one under /ferramentas', function () {
    $this->get('/consultor-ia')
        ->assertRedirect(route('tools.show', 'consultor-ia'))
        ->assertStatus(301);
});
