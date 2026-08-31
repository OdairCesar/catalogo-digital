<?php

namespace Database\Factories;

use App\Enums\ToolSubmissionStatus;
use App\Models\ToolSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ToolSubmission>
 */
class ToolSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_slug' => 'quanto-vale-minha-ideia',
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'messages' => [],
            'status' => ToolSubmissionStatus::InProgress,
            'questions_asked' => 0,
            'result' => null,
            'ai_model' => null,
            'ai_error' => null,
            'source_url' => $this->faker->url(),
            'read_at' => null,
        ];
    }

    public function generatingReport(): self
    {
        return $this->state(fn (): array => [
            'messages' => [
                ['role' => 'assistant', 'content' => 'Qual tipo de negócio você quer criar?'],
                ['role' => 'user', 'content' => 'Uma plataforma de assinaturas para pet shops.'],
            ],
            'status' => ToolSubmissionStatus::GeneratingReport,
            'questions_asked' => 5,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (): array => [
            'status' => ToolSubmissionStatus::Completed,
            'ai_model' => 'gpt-4.1',
            'result' => [
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
                ],
                'first_demoable_stage' => 'Já na etapa "Cobrança recorrente" é possível mostrar um fluxo completo de assinatura a clientes potenciais.',
                'risks' => ['Concorrência com soluções genéricas de assinatura.'],
                'competitive_advantage' => 'Atendimento e cobrança pensados para o nicho local.',
                'next_steps' => ['Validar preço com 5 clientes reais.'],
            ],
        ]);
    }
}
