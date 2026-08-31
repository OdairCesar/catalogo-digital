<?php

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Models\Company;
use App\Models\Section;

beforeEach(function () {
    Section::factory()->type(SectionType::FaqGroup)->create([
        'title' => 'Tamanhos e caimento',
        'sort_order' => 0,
        'data' => ['faq' => [
            ['question' => 'Quais tamanhos vocês têm?', 'answer' => 'Trabalhamos do 36 ao 52.'],
        ]],
    ]);

    Section::factory()->type(SectionType::FaqGroup)->create([
        'title' => 'Trocas e devoluções',
        'sort_order' => 1,
        'data' => ['faq' => [
            ['question' => 'Posso trocar se não servir?', 'answer' => 'Pode, em até 7 dias.'],
        ]],
    ]);

    Section::factory()->type(SectionType::FaqGroup)->create([
        'title' => 'Pagamento',
        'sort_order' => 2,
        'data' => ['faq' => [
            ['question' => 'Quais formas de pagamento vocês aceitam?', 'answer' => 'Pix, cartão ou parcelado.'],
        ]],
    ]);
});

test('faq page lists the section groups and questions from the database', function () {
    $this->get(route('faq.index'))
        ->assertOk()
        ->assertSee('Tamanhos e caimento')
        ->assertSee('Quais tamanhos vocês têm?')
        ->assertSee('Trocas e devoluções')
        ->assertSee('Pagamento');
});

test('faq page hides draft groups', function () {
    Section::factory()->type(SectionType::FaqGroup)->draft()->create([
        'title' => 'Grupo rascunho',
        'data' => ['faq' => [['question' => 'Pergunta rascunho?', 'answer' => 'Resposta.']]],
    ]);

    $this->get(route('faq.index'))
        ->assertOk()
        ->assertDontSee('Grupo rascunho');
});

test('faq page links to whatsapp when the company has a number configured', function () {
    Company::factory()->create(['whatsapp' => '11999999999', 'status' => PageStatus::Published]);

    $this->get(route('faq.index'))
        ->assertOk()
        ->assertSee('wa.me/5511999999999', false);
});

test('faq page falls back to the contact form when the company has no whatsapp number', function () {
    $this->get(route('faq.index'))
        ->assertOk()
        ->assertSee(route('contact.show'), false);
});
