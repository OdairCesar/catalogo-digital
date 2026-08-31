<?php

use App\Models\ToolSubmission;
use App\Services\Tools\ToolRegistry;
use App\Services\Tools\ToolTurnPromptBuilder;

test('the first turn always includes a user message so gemini never receives an empty conversation', function () {
    $submission = ToolSubmission::factory()->make(['messages' => []]);
    $tool = app(ToolRegistry::class)->findOrFail('quanto-vale-minha-ideia');

    $messages = (new ToolTurnPromptBuilder)->messagesFor($submission, $tool);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['role'])->toBe('system')
        ->and($messages[1]['role'])->toBe('user');
});

test('later turns do not duplicate the synthetic starter message', function () {
    $submission = ToolSubmission::factory()->make([
        'messages' => [
            ['role' => 'assistant', 'content' => 'Qual tipo de negócio você quer criar?'],
            ['role' => 'user', 'content' => 'Uma plataforma de assinaturas.'],
        ],
    ]);
    $tool = app(ToolRegistry::class)->findOrFail('quanto-vale-minha-ideia');

    $messages = (new ToolTurnPromptBuilder)->messagesFor($submission, $tool);

    expect($messages)->toHaveCount(3)
        ->and($messages[1]['role'])->toBe('assistant')
        ->and($messages[2]['role'])->toBe('user');
});
