<?php

use App\Models\City;
use App\Models\State;
use App\Services\Seo\ContentComposer;

beforeEach(function () {
    $this->composer = new ContentComposer;
    $this->city = City::factory()->make([
        'name' => 'Bauru',
        'state_id' => State::factory()->create(['uf' => 'SP'])->id,
        'region' => 'Centro-Oeste Paulista',
    ]);
});

test('compose replaces city tokens in a template string', function () {
    $result = $this->composer->compose(
        'Atendemos empresas de {cidade}/{uf}, na região {regiao}.',
        $this->city,
    );

    expect($result)->toBe('Atendemos empresas de Bauru/SP, na região Centro-Oeste Paulista.');
});

test('compose leaves templates without tokens untouched', function () {
    $result = $this->composer->compose('Texto sem tokens.', $this->city);

    expect($result)->toBe('Texto sem tokens.');
});

test('composeList applies token replacement to every item', function () {
    $result = $this->composer->composeList([
        'Presença digital para empresas de {cidade}',
        'Suporte local em {uf}',
    ], $this->city);

    expect($result)->toBe([
        'Presença digital para empresas de Bauru',
        'Suporte local em SP',
    ]);
});

test('composeFaq applies token replacement to both question and answer', function () {
    $result = $this->composer->composeFaq([
        ['question' => 'Vocês atendem {cidade}?', 'answer' => 'Sim, atendemos {cidade} e região.'],
    ], $this->city);

    expect($result)->toBe([
        ['question' => 'Vocês atendem Bauru?', 'answer' => 'Sim, atendemos Bauru e região.'],
    ]);
});

test('compose falls back to generic wording when no city is given', function () {
    $result = $this->composer->compose('Atendemos empresas de {cidade}/{uf}, região {regiao}.');

    expect($result)->toBe('Atendemos empresas de sua cidade/sua região, região sua região.');
});
