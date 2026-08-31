<?php

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Service;

test('static pages resolve', function (string $routeName) {
    $this->get(route($routeName))->assertOk();
})->with([
    'home',
    'about',
    'contact.show',
    'services.index',
    'cities.index',
    'states.index',
    'faq.index',
    'portfolio.index',
]);

test('service show page resolves for a published service', function () {
    $service = Service::factory()->create(['status' => PageStatus::Published]);

    $this->get(route('services.show', $service))->assertOk();
});

test('service show page 404s for a draft service', function () {
    $service = Service::factory()->create(['status' => PageStatus::Draft]);

    $this->get(route('services.show', $service))->assertNotFound();
});

test('city show page resolves for a published city', function () {
    $city = City::factory()->create(['status' => PageStatus::Published]);

    $this->get(route('cities.show', $city))->assertOk();
});

test('city show page 404s for a draft city', function () {
    $city = City::factory()->create(['status' => PageStatus::Draft]);

    $this->get(route('cities.show', $city))->assertNotFound();
});

test('landing page resolves for a published combination', function () {
    $service = Service::factory()->create(['status' => PageStatus::Published]);
    $city = City::factory()->create(['status' => PageStatus::Published]);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $this->get(route('landing.show', $landingPage))->assertOk();
});

test('landing page 404s when the underlying service or city is a draft', function () {
    $service = Service::factory()->create(['status' => PageStatus::Draft]);
    $city = City::factory()->create(['status' => PageStatus::Published]);
    $landingPage = LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();

    $this->get(route('landing.show', $landingPage))->assertNotFound();
});

test('contact form stores a lead and redirects back', function () {
    $this->post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '14999998888',
        'message' => 'Preciso de um orçamento.',
    ])->assertRedirect(route('contact.show'));

    $this->assertDatabaseHas('leads', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});
