<?php

use App\Exceptions\AiGenerationException;
use App\Services\Ai\JsonSchema;
use App\Services\Tools\ToolReportValidator;

function validatorSchema(array $schema): JsonSchema
{
    return new JsonSchema(name: 'test_schema', schema: $schema);
}

test('it validates and returns a well-formed payload', function () {
    $schema = validatorSchema([
        'type' => 'object',
        'required' => ['summary', 'score', 'tags'],
        'properties' => [
            'summary' => ['type' => 'string'],
            'score' => ['type' => 'number'],
            'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
        ],
    ]);

    $result = (new ToolReportValidator)->validate(json_encode([
        'summary' => 'Resumo de teste.',
        'score' => 87,
        'tags' => ['a', 'b'],
    ]), $schema);

    expect($result)->toBe([
        'summary' => 'Resumo de teste.',
        'score' => 87.0,
        'tags' => ['a', 'b'],
    ]);
});

test('it rejects a payload missing a required field even when that field is absent from properties', function () {
    $schema = validatorSchema([
        'type' => 'object',
        'required' => ['summary', 'internal_flag'],
        'properties' => [
            'summary' => ['type' => 'string'],
        ],
    ]);

    (new ToolReportValidator)->validate(json_encode(['summary' => 'Resumo de teste.']), $schema);
})->throws(AiGenerationException::class);

test('it rejects a non-json payload', function () {
    (new ToolReportValidator)->validate('not json', validatorSchema([
        'type' => 'object',
        'required' => [],
        'properties' => [],
    ]));
})->throws(AiGenerationException::class);

test('it validates nested arrays of objects', function () {
    $schema = validatorSchema([
        'type' => 'object',
        'required' => ['steps'],
        'properties' => [
            'steps' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'required' => ['name'],
                    'properties' => [
                        'name' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
    ]);

    $result = (new ToolReportValidator)->validate(json_encode([
        'steps' => [['name' => 'Etapa 1'], ['name' => 'Etapa 2']],
    ]), $schema);

    expect($result['steps'])->toBe([
        ['name' => 'Etapa 1'],
        ['name' => 'Etapa 2'],
    ]);
});
