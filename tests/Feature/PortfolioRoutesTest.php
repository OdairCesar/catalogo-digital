<?php

use App\Models\Section;
use App\Models\Service;

test('portfolio show page resolves for a published item', function () {
    $item = Section::factory()->portfolio()->published()->create();

    $this->get(route('portfolio.show', $item->slug))->assertOk();
});

test('portfolio show page 404s for a draft item', function () {
    $item = Section::factory()->portfolio()->draft()->create();

    $this->get(route('portfolio.show', $item->slug))->assertNotFound();
});

test('portfolio index only lists published items', function () {
    $published = Section::factory()->portfolio()->published()->create(['title' => 'Projeto Publicado']);
    Section::factory()->portfolio()->draft()->create(['title' => 'Projeto Rascunho']);

    $this->get(route('portfolio.index'))
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee('Projeto Rascunho');
});

test('portfolio filtered by service only lists items linked to that service', function () {
    $service = Service::factory()->create();
    $otherService = Service::factory()->create();

    $matching = Section::factory()->portfolio()->published()->create(['service_id' => $service->id, 'title' => 'Projeto Do Servico']);
    Section::factory()->portfolio()->published()->create(['service_id' => $otherService->id, 'title' => 'Projeto De Outro Servico']);

    $this->get(route('portfolio.service', $service))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee('Projeto De Outro Servico');
});
