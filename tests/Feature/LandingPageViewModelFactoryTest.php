<?php

use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;
use App\Services\Landing\LandingPageViewModelFactory;

test('it builds a fully composed view model from a landing page', function () {
    $service = Service::factory()->create([
        'title' => 'Criação de Sites',
        'subtitle' => 'Presença digital para {cidade}',
        'description' => 'Desenvolvemos sites para empresas de {cidade}.',
        'benefits' => ['Design responsivo em {cidade}'],
        'faq' => [['question' => 'Atendem {cidade}?', 'answer' => 'Sim, atendemos {cidade}.']],
    ]);
    $city = City::factory()->create(['name' => 'Bauru']);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $vm = app(LandingPageViewModelFactory::class)->make($landingPage);

    expect($vm->h1)->toBe('Criação de Sites em Bauru')
        ->and($vm->subtitle)->toBe('Presença digital para Bauru')
        ->and($vm->intro)->toBe('Desenvolvemos sites para empresas de Bauru.')
        ->and($vm->benefits)->toBe(['Design responsivo em Bauru'])
        ->and($vm->faq[0]['question'])->toBe('Atendem Bauru?')
        ->and($vm->seo->title)->toBe('Criação de Sites em Bauru | Fit By Cae')
        ->and($vm->breadcrumbs)->toHaveCount(3)
        ->and($vm->jsonLd)->not->toBeEmpty();
});

test('manual overrides on the landing page take precedence over composed content', function () {
    $service = Service::factory()->create();
    $city = City::factory()->create(['name' => 'Bauru']);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $landingPage->update([
        'custom_h1' => 'H1 customizado',
        'custom_subtitle' => 'Subtítulo customizado',
        'custom_intro' => 'Introdução customizada',
        'custom_cta' => 'Fale agora',
    ]);

    $vm = app(LandingPageViewModelFactory::class)->make($landingPage->fresh());

    expect($vm->h1)->toBe('H1 customizado')
        ->and($vm->subtitle)->toBe('Subtítulo customizado')
        ->and($vm->intro)->toBe('Introdução customizada')
        ->and($vm->ctaLabel)->toBe('Fale agora');
});

test('it caches the view model and reflects changes after an update invalidates the cache key', function () {
    $service = Service::factory()->create();
    $city = City::factory()->create();
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $factory = app(LandingPageViewModelFactory::class);

    $first = $factory->make($landingPage);
    expect($first->h1)->not->toBe('H1 customizado');

    $landingPage->update(['custom_h1' => 'H1 customizado']);

    $second = $factory->make($landingPage->fresh());
    expect($second->h1)->toBe('H1 customizado');
});
