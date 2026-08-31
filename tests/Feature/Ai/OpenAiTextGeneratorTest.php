<?php

use App\Services\Ai\JsonSchema;
use App\Services\Ai\OpenAi\OpenAiTextGenerator;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;

test('generate returns the message content and model from the openai response', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'model' => 'gpt-4.1',
            'choices' => [[
                'message' => ['content' => '{"title":"Projeto X","excerpt":"Resumo"}'],
            ]],
        ]),
    ]);

    $schema = new JsonSchema(name: 'portfolio_copy', schema: [
        'type' => 'object',
        'required' => ['title', 'excerpt'],
        'properties' => [
            'title' => ['type' => 'string'],
            'excerpt' => ['type' => 'string'],
        ],
    ]);

    $result = app(OpenAiTextGenerator::class)->generate(
        [['role' => 'user', 'content' => 'Gere um copy']],
        $schema,
        0.7,
    );

    expect($result->content)->toBe('{"title":"Projeto X","excerpt":"Resumo"}')
        ->and($result->model)->toBe('gpt-4.1');
});

test('generate applies additionalProperties false to every object node of the schema', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [['message' => ['content' => '{}']]],
        ]),
    ]);

    $schema = new JsonSchema(name: 'consultation_report', schema: [
        'type' => 'object',
        'required' => ['delivery_stages'],
        'properties' => [
            'delivery_stages' => [
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

    app(OpenAiTextGenerator::class)->generate(
        [['role' => 'user', 'content' => 'Gere um relatório']],
        $schema,
        0.5,
    );

    OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
        $rootSchema = $parameters['response_format']['json_schema']['schema'];
        $itemSchema = $rootSchema['properties']['delivery_stages']['items'];

        return $method === 'create'
            && $parameters['response_format']['json_schema']['strict'] === true
            && $rootSchema['additionalProperties'] === false
            && $itemSchema['additionalProperties'] === false;
    });
});
