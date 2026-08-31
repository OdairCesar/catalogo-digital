<?php

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\Service;
use App\Models\State;
use App\Services\Landing\ServiceCityViewModelFactory;
use Illuminate\Support\Facades\Storage;

test('makeForService builds a fully composed view model from a service', function () {
    $service = Service::factory()->create([
        'title' => 'Criação de Sites',
        'subtitle' => 'Presença digital para {cidade}',
        'description' => 'Desenvolvemos sites para empresas de {cidade}.',
        'benefits' => ['Design responsivo em {cidade}'],
        'faq' => [['question' => 'Atendem {cidade}?', 'answer' => 'Sim, atendemos {cidade}.']],
    ]);

    $vm = app(ServiceCityViewModelFactory::class)->makeForService($service);

    expect($vm->title)->toBe('Criação de Sites')
        ->and($vm->subtitle)->toBe('Presença digital para sua cidade')
        ->and($vm->description)->toBe('Desenvolvemos sites para empresas de sua cidade.')
        ->and($vm->benefits)->toBe(['Design responsivo em sua cidade'])
        ->and($vm->faq[0]['question'])->toBe('Atendem sua cidade?')
        ->and($vm->seo->title)->toBe('Criação de Sites — Fit By Cae')
        ->and($vm->heroImageUrl)->toBeNull()
        ->and($vm->breadcrumbs)->toHaveCount(3)
        ->and($vm->jsonLd)->not->toBeEmpty();
});

test('makeForService resolves the hero image url from the cloudinary disk when a hero image is set', function () {
    Storage::fake('cloudinary');

    $service = Service::factory()->create(['hero_image' => 'services/example-hero']);

    $vm = app(ServiceCityViewModelFactory::class)->makeForService($service);

    expect($vm->heroImageUrl)->toBe(Storage::disk('cloudinary')->url('services/example-hero'));
});

test('makeForCity builds a fully composed view model from a city', function () {
    $service = Service::factory()->create(['subtitle' => 'Presença digital em {cidade}/{uf}']);
    $city = City::factory()->create(['name' => 'Bauru', 'state_id' => State::factory()->create(['uf' => 'SP'])->id]);

    $vm = app(ServiceCityViewModelFactory::class)->makeForCity($city);

    expect($vm->name)->toBe('Bauru')
        ->and($vm->seo->title)->toBe('Tecnologia em Bauru/SP — Fit By Cae')
        ->and($vm->breadcrumbs)->toHaveCount(3)
        ->and($vm->jsonLd)->not->toBeEmpty()
        ->and($vm->landingPages)->toHaveCount(1)
        ->and($vm->landingPages[0]['label'])->toBe($service->title)
        ->and($vm->landingPages[0]['subtitle'])->toBe('Presença digital em Bauru/SP');
});

test('makeForCity only lists published landing pages', function () {
    Service::factory()->create(['status' => PageStatus::Draft]);
    $city = City::factory()->create();

    $vm = app(ServiceCityViewModelFactory::class)->makeForCity($city);

    expect($vm->landingPages)->toBeEmpty();
});
