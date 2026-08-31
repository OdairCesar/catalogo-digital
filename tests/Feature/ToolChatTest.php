<?php

use App\Enums\ToolSubmissionStatus;
use App\Jobs\GenerateToolReport;
use App\Livewire\ToolChat;
use App\Models\ToolSubmission;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

function fakeToolTurnResponse(string $reply, bool $readyForReport = false): CreateResponse
{
    return CreateResponse::fake([
        'model' => 'gpt-4.1',
        'choices' => [[
            'message' => [
                'content' => json_encode([
                    'reply' => $reply,
                    'ready_for_report' => $readyForReport,
                ]),
            ],
        ]],
    ]);
}

function startToolChat(string $slug = 'quanto-vale-minha-ideia', string $name = 'Maria Silva', string $email = 'maria@example.com', string $phone = '11999999999')
{
    return Livewire::test(ToolChat::class, ['slug' => $slug])
        ->assertSet('stage', 'name')
        ->set('input', $name)
        ->call('submitInput')
        ->assertSet('stage', 'email')
        ->set('input', $email)
        ->call('submitInput')
        ->assertSet('stage', 'phone')
        ->set('input', $phone)
        ->call('submitInput')
        ->assertSet('stage', 'ai');
}

test('a visitor can start a tool chat and the ai asks the first question', function () {
    OpenAI::fake([
        fakeToolTurnResponse('Qual tipo de negócio você quer criar?'),
    ]);

    $component = startToolChat()->assertHasNoErrors();

    $submission = ToolSubmission::sole();

    expect($submission->tool_slug)->toBe('quanto-vale-minha-ideia')
        ->and($submission->name)->toBe('Maria Silva')
        ->and($submission->email)->toBe('maria@example.com')
        ->and($submission->phone)->toBe('11999999999')
        ->and($submission->messages)->toHaveCount(1)
        ->and($submission->messages[0]['role'])->toBe('assistant')
        ->and($submission->messages[0]['content'])->toBe('Qual tipo de negócio você quer criar?')
        ->and($submission->status)->toBe(ToolSubmissionStatus::InProgress);

    $component->assertSee('Maria Silva')
        ->assertSee('Qual tipo de negócio você quer criar?');
});

test('an invalid email keeps the visitor on the email stage', function () {
    Livewire::test(ToolChat::class, ['slug' => 'quanto-vale-minha-ideia'])
        ->set('input', 'Maria Silva')
        ->call('submitInput')
        ->set('input', 'not-an-email')
        ->call('submitInput')
        ->assertSet('stage', 'email')
        ->assertHasErrors('input');
});

test('sending a message that the ai marks as ready dispatches the report job', function () {
    Queue::fake();

    OpenAI::fake([
        fakeToolTurnResponse('Qual tipo de negócio você quer criar?'),
        fakeToolTurnResponse('Perfeito, já tenho tudo que preciso!', readyForReport: true),
    ]);

    $component = startToolChat()
        ->set('input', 'Uma plataforma de assinaturas para pet shops.')
        ->call('submitInput')
        ->assertSet('step', 'generating');

    $submission = ToolSubmission::sole();

    expect($submission->status)->toBe(ToolSubmissionStatus::GeneratingReport)
        ->and($submission->questions_asked)->toBe(1)
        ->and($submission->messages)->toHaveCount(3);

    Queue::assertPushed(GenerateToolReport::class, fn (GenerateToolReport $job): bool => $job->submission->is($submission));
});

test('the interview is forced to finish once the maximum number of questions is reached', function () {
    Queue::fake();

    $maxQuestions = config('tools.quanto-vale-minha-ideia.max_followup_questions');

    $responses = [fakeToolTurnResponse('Primeira pergunta adaptativa')];

    for ($i = 0; $i < $maxQuestions; $i++) {
        $responses[] = fakeToolTurnResponse("Pergunta {$i}", readyForReport: false);
    }

    OpenAI::fake($responses);

    $component = startToolChat();

    for ($i = 0; $i < $maxQuestions; $i++) {
        $component->set('input', "Resposta {$i}")->call('submitInput');
    }

    $component->assertSet('step', 'generating');

    $submission = ToolSubmission::sole();

    expect($submission->status)->toBe(ToolSubmissionStatus::GeneratingReport)
        ->and($submission->questions_asked)->toBe($maxQuestions);
});

test('starting a tool chat is rate limited per ip address', function () {
    OpenAI::fake([
        fakeToolTurnResponse('Pergunta 1'),
        fakeToolTurnResponse('Pergunta 2'),
        fakeToolTurnResponse('Pergunta 3'),
        fakeToolTurnResponse('Pergunta 4'),
        fakeToolTurnResponse('Pergunta 5'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        startToolChat(name: "Visitante {$i}", email: "visitante{$i}@example.com");
    }

    Livewire::test(ToolChat::class, ['slug' => 'quanto-vale-minha-ideia'])
        ->set('input', 'Visitante extra')
        ->call('submitInput')
        ->set('input', 'extra@example.com')
        ->call('submitInput')
        ->set('input', '11999999999')
        ->call('submitInput')
        ->assertSet('stage', 'phone')
        ->assertHasErrors('input');

    expect(ToolSubmission::count())->toBe(5);
});

test('the poll transitions to the result step once the job completes', function () {
    $submission = ToolSubmission::factory()->generatingReport()->create();

    Livewire::test(ToolChat::class, ['slug' => $submission->tool_slug])
        ->set('submission', $submission)
        ->set('step', 'generating')
        ->call('pollReportStatus')
        ->assertSet('step', 'generating');

    $submission->update([
        'status' => ToolSubmissionStatus::Completed,
        'result' => ToolSubmission::factory()->completed()->make()->result,
    ]);

    Livewire::test(ToolChat::class, ['slug' => $submission->tool_slug])
        ->set('submission', $submission)
        ->set('step', 'generating')
        ->call('pollReportStatus')
        ->assertSet('step', 'result')
        ->assertSee($submission->result['mvp_cost_estimate']);
});

test('the poll transitions to the failed step if report generation fails', function () {
    $submission = ToolSubmission::factory()->generatingReport()->create();
    $submission->update(['status' => ToolSubmissionStatus::Failed, 'ai_error' => 'Falha na IA']);

    Livewire::test(ToolChat::class, ['slug' => $submission->tool_slug])
        ->set('submission', $submission)
        ->set('step', 'generating')
        ->call('pollReportStatus')
        ->assertSet('step', 'failed');
});
