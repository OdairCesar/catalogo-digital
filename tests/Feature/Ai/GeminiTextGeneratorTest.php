<?php

use App\Exceptions\AiGenerationException;
use App\Services\Ai\Gemini\GeminiTextGenerator;
use App\Services\Ai\JsonSchema;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;

test('generate returns the text produced by gemini for the given conversation', function () {
    config(['services.gemini.model' => 'gemini-2.5-flash']);

    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => '{"reply":"Qual o principal objetivo do projeto?","ready_for_report":false}']],
                    'role' => 'model',
                ],
            ]],
        ]),
    ]);

    $schema = new JsonSchema(name: 'consultation_turn', schema: [
        'type' => 'object',
        'required' => ['reply', 'ready_for_report'],
        'properties' => [
            'reply' => ['type' => 'string'],
            'ready_for_report' => ['type' => 'boolean'],
        ],
    ]);

    $result = app(GeminiTextGenerator::class)->generate(
        [
            ['role' => 'system', 'content' => 'Você é um analista de negócios da OD Tec.'],
            ['role' => 'user', 'content' => 'Quero criar um app de agendamento.'],
            ['role' => 'assistant', 'content' => 'Qual é o público-alvo?'],
            ['role' => 'user', 'content' => 'Clínicas pequenas.'],
        ],
        $schema,
        0.6,
    );

    expect($result->content)->toBe('{"reply":"Qual o principal objetivo do projeto?","ready_for_report":false}')
        ->and($result->model)->toBe('gemini-2.5-flash');
});

test('generate merges multiple system messages and excludes them from the conversation turns', function () {
    config(['services.gemini.model' => 'gemini-2.5-flash']);

    Gemini::fake([
        GenerateContentResponse::fake([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => '{"reply":"ok","ready_for_report":false}']],
                    'role' => 'model',
                ],
            ]],
        ]),
    ]);

    $schema = new JsonSchema(name: 'consultation_turn', schema: [
        'type' => 'object',
        'required' => ['reply', 'ready_for_report'],
        'properties' => [
            'reply' => ['type' => 'string'],
            'ready_for_report' => ['type' => 'boolean'],
        ],
    ]);

    app(GeminiTextGenerator::class)->generate(
        [
            ['role' => 'system', 'content' => 'Primeira instrução.'],
            ['role' => 'system', 'content' => 'Segunda instrução.'],
            ['role' => 'user', 'content' => 'Olá.'],
        ],
        $schema,
        0.6,
    );

    Gemini::generativeModel(model: 'gemini-2.5-flash')->assertSent(
        fn (string $method, array $args): bool => $method === 'generateContent' && count($args) === 1
    );
});

test('generate throws when gemini returns no usable text content', function () {
    Gemini::fake([
        GenerateContentResponse::from([
            'candidates' => [],
            'usageMetadata' => ['totalTokenCount' => 1],
        ]),
    ]);

    $schema = new JsonSchema(name: 'consultation_turn', schema: [
        'type' => 'object',
        'required' => ['reply'],
        'properties' => ['reply' => ['type' => 'string']],
    ]);

    app(GeminiTextGenerator::class)->generate(
        [['role' => 'user', 'content' => 'Olá']],
        $schema,
        0.6,
    );
})->throws(AiGenerationException::class);
