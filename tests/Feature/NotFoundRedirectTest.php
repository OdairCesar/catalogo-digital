<?php

use App\Enums\PageStatus;
use App\Models\Service;

test('an unmatched url redirects straight to the home page', function () {
    // Precisa ter vários segmentos para não cair na rota curinga de SEO
    // programático (`/{landingPage}`), que casa qualquer segmento único.
    $this->get('/isso/definitivamente/nao/existe/em/lugar/nenhum')
        ->assertRedirect(route('home'));
});

test('a draft service page still 404s but renders the auto-redirect notice', function () {
    $service = Service::factory()->create(['status' => PageStatus::Draft]);

    $this->get(route('services.show', $service))
        ->assertNotFound()
        ->assertSee('redirecionado para a página inicial');
});

test('an unmatched admin url 404s without redirecting to the public home', function () {
    $this->get('/admin/isso-nao-existe')
        ->assertNotFound();
});
