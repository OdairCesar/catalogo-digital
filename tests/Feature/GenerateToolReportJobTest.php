<?php

use App\Enums\ToolSubmissionStatus;
use App\Jobs\GenerateToolReport;
use App\Mail\NewToolSubmissionCompleted;
use App\Models\ToolSubmission;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

function fakeToolReportResponse(): CreateResponse
{
    return CreateResponse::fake([
        'model' => 'gpt-4.1',
        'choices' => [[
            'message' => [
                'content' => json_encode([
                    'estimated_monthly_revenue_min' => 'R$ 8.000',
                    'estimated_monthly_revenue_max' => 'R$ 25.000',
                    'mvp_cost_estimate' => 'R$ 20.000 a R$ 35.000',
                    'mvp_timeframe' => '2 a 3 meses',
                    'recommended_features' => ['Cadastro de clientes', 'Cobrança recorrente'],
                    'roadmap' => [
                        ['phase' => 'MVP', 'description' => 'Cadastro, cobrança e painel básico.'],
                    ],
                    'viability_notes' => 'Ideia com bom potencial de recorrência.',
                    'executive_summary' => 'Plataforma de assinaturas com bom potencial de recorrência.',
                    'target_audience' => 'Pequenos negócios locais que ainda cobram manualmente.',
                    'business_model' => 'Assinatura mensal recorrente.',
                    'revenue_streams' => ['Assinatura mensal', 'Taxa de setup opcional'],
                    'delivery_stages' => [
                        ['name' => 'Configuração inicial', 'description' => 'Cadastro e autenticação.', 'timeframe' => '3 semanas', 'investment' => 'R$ 8.000'],
                        ['name' => 'Cobrança recorrente', 'description' => 'Integração de pagamento e assinaturas.', 'timeframe' => '3 semanas', 'investment' => 'R$ 12.000'],
                        ['name' => 'Painel e testes', 'description' => 'Painel do cliente e testes finais.', 'timeframe' => '2 semanas', 'investment' => 'R$ 8.000'],
                    ],
                    'first_demoable_stage' => 'Já na etapa "Cobrança recorrente" é possível mostrar um fluxo completo de assinatura a clientes potenciais.',
                    'risks' => ['Concorrência com soluções genéricas de assinatura.'],
                    'competitive_advantage' => 'Atendimento e cobrança pensados para o nicho local.',
                    'next_steps' => ['Validar preço com 5 clientes reais.'],
                ]),
            ],
        ]],
    ]);
}

test('generating a tool report succeeds and notifies the team', function () {
    Mail::fake();
    OpenAI::fake([fakeToolReportResponse()]);

    $submission = ToolSubmission::factory()->generatingReport()->create();

    Bus::dispatchSync(new GenerateToolReport($submission));

    $submission->refresh();

    expect($submission->status)->toBe(ToolSubmissionStatus::Completed)
        ->and($submission->ai_model)->toBe('gpt-4.1')
        ->and($submission->ai_error)->toBeNull()
        ->and($submission->result['mvp_cost_estimate'])->toBe('R$ 20.000 a R$ 35.000')
        ->and($submission->result['roadmap'][0]['phase'])->toBe('MVP');

    Mail::assertSent(NewToolSubmissionCompleted::class, fn (NewToolSubmissionCompleted $mail): bool => $mail->submission->is($submission));
});

test('an invalid ai response marks the tool submission as failed', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [[
                'message' => [
                    'content' => json_encode(['estimated_monthly_revenue_min' => 'Só isso, faltando o resto.']),
                ],
            ]],
        ]),
    ]);

    $submission = ToolSubmission::factory()->generatingReport()->create();

    Bus::dispatchSync(new GenerateToolReport($submission));

    $submission->refresh();

    expect($submission->status)->toBe(ToolSubmissionStatus::Failed)
        ->and($submission->ai_error)->not->toBeNull();
});

test('the failed hook marks the tool submission as failed with the exception message', function () {
    $submission = ToolSubmission::factory()->generatingReport()->create();

    (new GenerateToolReport($submission))->failed(new Exception('Falha de conexão com a OpenAI'));

    $submission->refresh();

    expect($submission->status)->toBe(ToolSubmissionStatus::Failed)
        ->and($submission->ai_error)->toBe('Falha de conexão com a OpenAI');
});
