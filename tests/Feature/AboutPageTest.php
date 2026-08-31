<?php

use App\Enums\SectionType;
use App\Models\Section;

test('about page renders its content from the database', function () {
    Section::factory()->type(SectionType::About)->create([
        'title' => 'Título de teste da Sobre',
        'data' => [
            'intro_paragraphs' => [['paragraph' => 'Parágrafo de teste.']],
            'missao_text' => 'Missão de teste.',
            'quem_escolhe_text' => 'Texto de quem escolhe de teste.',
            'values' => [['title' => 'Valor de teste', 'desc' => 'Descrição de teste.']],
            'manifesto_paragraphs' => [['paragraph' => 'Manifesto de teste.']],
            'manifesto_tagline' => 'Tagline de teste.',
            'cta_title' => 'CTA de teste',
            'cta_description' => 'Descrição do CTA de teste.',
            'cta_button_label' => 'Botão de teste',
        ],
    ]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Título de teste da Sobre')
        ->assertSee('Parágrafo de teste.')
        ->assertSee('Missão de teste.')
        ->assertSee('Texto de quem escolhe de teste.')
        ->assertSee('Valor de teste')
        ->assertSee('Descrição de teste.')
        ->assertSee('Manifesto de teste.')
        ->assertSee('Tagline de teste.')
        ->assertSee('CTA de teste')
        ->assertSee('Descrição do CTA de teste.');
});

test('about page falls back to its default copy when no section exists yet', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('A Fit By Cae não vende só peças de treino — vende autoestima');
});
