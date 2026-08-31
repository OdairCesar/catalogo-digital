<?php

use App\Enums\SectionType;
use App\Models\Company;
use App\Models\Section;
use Illuminate\Support\Facades\Storage;

test('home page renders the hero, trust bar, and whatsapp banner from the database', function () {
    Section::factory()->type(SectionType::HomeHero)->create([
        'title' => 'Título de teste',
        'content' => 'Parágrafo de teste.',
        'data' => ['badge' => 'Selo de teste', 'cta_label' => 'Botão de teste'],
    ]);

    Section::factory()->type(SectionType::HomeTrustBar)->create([
        'data' => ['items' => [['title' => 'Item 1', 'subtitle' => 'Sub 1']]],
    ]);

    Section::factory()->type(SectionType::HomeWhatsappBanner)->create([
        'title' => 'Banner de teste',
        'content' => 'Texto do banner.',
        'data' => ['subtitle' => 'Subtítulo do banner', 'button_label' => 'Botão do banner'],
    ]);

    Company::factory()->create(['whatsapp' => '11999999999']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Selo de teste')
        ->assertSee('Título de teste')
        ->assertSee('Parágrafo de teste.')
        ->assertSee('Botão de teste')
        ->assertSee('Item 1')
        ->assertSee('Sub 1')
        ->assertSee('Banner de teste')
        ->assertSee('Subtítulo do banner')
        ->assertSee('Texto do banner.')
        ->assertSee('Botão do banner');
});

test('home page lists active testimonials in order and hides drafts', function () {
    Section::factory()->type(SectionType::Testimonial)->create([
        'content' => 'Segundo depoimento.',
        'sort_order' => 1,
        'data' => ['author_name' => 'Segunda Pessoa'],
    ]);

    Section::factory()->type(SectionType::Testimonial)->create([
        'content' => 'Primeiro depoimento.',
        'sort_order' => 0,
        'data' => ['author_name' => 'Primeira Pessoa'],
    ]);

    Section::factory()->type(SectionType::Testimonial)->draft()->create([
        'content' => 'Depoimento rascunho.',
        'data' => ['author_name' => 'Pessoa Rascunho'],
    ]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertSee('Primeiro depoimento.')
        ->assertSee('Segundo depoimento.')
        ->assertDontSee('Depoimento rascunho.');

    $content = $response->getContent();
    expect(strpos($content, 'Primeiro depoimento.'))->toBeLessThan(strpos($content, 'Segundo depoimento.'));
});

test('home page renders extra fields added to the hero and to a testimonial', function () {
    Section::factory()->type(SectionType::HomeHero)->create([
        'extra_fields' => [['label' => 'Promoção', 'value' => 'Frete grátis em agosto']],
    ]);

    Section::factory()->type(SectionType::Testimonial)->create([
        'content' => 'Depoimento com campo extra.',
        'data' => ['author_name' => 'Pessoa Extra'],
        'extra_fields' => [['label' => 'Cidade', 'value' => 'São Paulo']],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Promoção')
        ->assertSee('Frete grátis em agosto')
        ->assertSee('Cidade')
        ->assertSee('São Paulo');
});

test('home page renders the instagram block title and description from the database', function () {
    Section::factory()->type(SectionType::Instagram)->create([
        'title' => 'Bloco Instagram de teste',
        'content' => 'Descrição de teste.',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Bloco Instagram de teste')
        ->assertSee('Descrição de teste.');
});

test('home page renders published instagram posts as linked images and hides drafts', function () {
    Storage::fake('cloudinary');

    Section::factory()->type(SectionType::Instagram)->create();

    Section::factory()->type(SectionType::InstagramPost)->create([
        'image' => 'instagram/published.jpg',
        'data' => ['link' => 'https://www.instagram.com/p/published/'],
        'sort_order' => 0,
    ]);

    Section::factory()->type(SectionType::InstagramPost)->draft()->create([
        'image' => 'instagram/draft.jpg',
        'sort_order' => 1,
    ]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertSee(Storage::disk('cloudinary')->url('instagram/published.jpg'), false)
        ->assertSee('href="https://www.instagram.com/p/published/"', false)
        ->assertDontSee(Storage::disk('cloudinary')->url('instagram/draft.jpg'), false);
});
