<?php

use App\Enums\SectionType;
use App\Filament\Pages\SectionTypeSettings;
use App\Models\Section;
use App\Models\SectionTypeSetting;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

test('disabling a home block section type hides it from the home page but leaves others untouched', function () {
    Section::factory()->type(SectionType::HomeHero)->create(['title' => 'Título do hero']);
    Section::factory()->type(SectionType::HomeTrustBar)->create();
    Section::factory()->type(SectionType::Testimonial)->create(['content' => 'Depoimento de teste.']);

    SectionTypeSetting::query()->create(['type' => SectionType::HomeHero, 'enabled' => false]);
    SectionTypeSetting::query()->create(['type' => SectionType::Testimonial, 'enabled' => false]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertDontSee('Título do hero')
        ->assertDontSee('Depoimento de teste.');
});

test('disabling about and faq 404s their routes and hides them from the nav', function () {
    SectionTypeSetting::query()->create(['type' => SectionType::About, 'enabled' => false]);
    SectionTypeSetting::query()->create(['type' => SectionType::FaqGroup, 'enabled' => false]);

    $this->get(route('about'))->assertNotFound();
    $this->get(route('faq.index'))->assertNotFound();

    $response = $this->get(route('home'))->assertOk();

    $response->assertDontSee('href="'.route('about').'"', false)
        ->assertDontSee('href="'.route('faq.index').'"', false);
});

test('disabling portfolio 404s its routes', function () {
    $item = Section::factory()->portfolio()->published()->create();
    $service = Service::factory()->create();

    SectionTypeSetting::query()->create(['type' => SectionType::Portfolio, 'enabled' => false]);

    $this->get(route('portfolio.index'))->assertNotFound();
    $this->get(route('portfolio.show', $item->slug))->assertNotFound();
    $this->get(route('portfolio.service', $service))->assertNotFound();
});

test('sitemap.xml excludes about, faq and portfolio when disabled', function () {
    $item = Section::factory()->portfolio()->published()->create();

    SectionTypeSetting::query()->create(['type' => SectionType::About, 'enabled' => false]);
    SectionTypeSetting::query()->create(['type' => SectionType::FaqGroup, 'enabled' => false]);
    SectionTypeSetting::query()->create(['type' => SectionType::Portfolio, 'enabled' => false]);

    $response = $this->get(route('sitemap'))->assertOk();

    $response->assertDontSee(route('about'), false)
        ->assertDontSee(route('faq.index'), false)
        ->assertDontSee(route('portfolio.index'), false)
        ->assertDontSee(route('portfolio.show', $item->slug), false)
        ->assertSee(route('home'), false);
});

test('all section types render normally when enabled', function () {
    $response = $this->get(route('home'))->assertOk();

    $response->assertSee('href="'.route('about').'"', false)
        ->assertSee('href="'.route('faq.index').'"', false);

    $this->get(route('about'))->assertOk();
    $this->get(route('faq.index'))->assertOk();
    $this->get(route('portfolio.index'))->assertOk();
});

test('the section type settings page toggles a section off and busts the cache', function () {
    $this->actingAs(User::factory()->admin()->create());

    expect(SectionTypeSetting::isEnabled(SectionType::Testimonial))->toBeTrue();

    Livewire::test(SectionTypeSettings::class)
        ->fillForm([SectionType::Testimonial->value => false])
        ->call('save');

    expect(SectionTypeSetting::isEnabled(SectionType::Testimonial))->toBeFalse();
});
