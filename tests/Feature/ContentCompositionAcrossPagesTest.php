<?php

use App\Models\City;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Models\State;

test('services index composes subtitles generically', function () {
    Service::factory()->create(['subtitle' => 'Presença digital em {cidade}']);

    $this->get(route('services.index'))
        ->assertOk()
        ->assertSee('Presença digital em sua cidade')
        ->assertDontSee('{cidade}', false);
});

test('service show composes subtitle, description, benefits and faq generically', function () {
    $service = Service::factory()->create([
        'subtitle' => 'Para empresas de {cidade}',
        'description' => 'Atuamos em {cidade} e {regiao}.',
        'benefits' => ['Suporte local em {cidade}'],
        'faq' => [['question' => 'Atendem {cidade}?', 'answer' => 'Sim, atendemos {cidade}.']],
    ]);

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('Para empresas de sua cidade')
        ->assertSee('Atuamos em sua cidade e sua região.')
        ->assertSee('Suporte local em sua cidade')
        ->assertSee('Atendem sua cidade?')
        ->assertDontSee('{cidade}', false)
        ->assertDontSee('{regiao}', false);
});

test('cluster city page composes the H1 and meta title without leaking the token or duplicating the city name', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'title' => 'Desenvolvimento de Sites Personalizados em {cidade}',
        'subtitle' => 'E-commerce rápido para empresas de {cidade}.',
    ]);
    $city = City::factory()->create(['name' => 'Mauá']);
    $pivot = ServiceClusterLandingPage::where('service_cluster_id', $cluster->id)->where('city_id', $city->id)->sole();

    $this->get(route('services.clusters.show', [$service, $pivot->slug]))
        ->assertOk()
        ->assertSee('Desenvolvimento de Sites Personalizados em Mauá')
        ->assertDontSee('{cidade}', false)
        ->assertDontSee('Mauá em Mauá', false);
});

test('city show composes the service subtitle with the actual city context', function () {
    $service = Service::factory()->create(['subtitle' => 'Presença digital em {cidade}/{uf}']);
    $city = City::factory()->create(['name' => 'Bauru', 'state_id' => State::factory()->create(['uf' => 'SP'])->id]);

    $this->get(route('cities.show', $city))
        ->assertOk()
        ->assertSee('Presença digital em Bauru/SP')
        ->assertDontSee('{cidade}', false);
});
