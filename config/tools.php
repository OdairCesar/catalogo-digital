<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ferramentas de IA (/ferramentas)
    |--------------------------------------------------------------------------
    |
    | Cada chave é o slug público da ferramenta. "interview_system_prompt"
    | orienta a IA durante o chat (uma pergunta por vez, decidindo quando já
    | tem informação suficiente). "report_system_prompt" + "report_response_
    | format" orientam a geração do resultado final, já validado
    | estruturalmente contra o schema antes de ser salvo.
    |
    | Os relatórios são propositalmente ricos (resumo executivo, etapas com
    | prazo/investimento, riscos, próximos passos...): a tela do site mostra
    | só o essencial (ver result_view), mas o PDF baixado mostra o relatório
    | completo, já que quem baixa o PDF quer se aprofundar. Nenhuma ferramenta
    | sugere tamanho de equipe — quem executa é sempre a equipe da OD Tec.
    |
    */

    'quanto-vale-minha-ideia' => [
        'title' => 'Quanto vale sua ideia?',
        'tagline' => 'Estime o potencial de faturamento e o investimento inicial do seu negócio.',
        'description' => 'Responda algumas perguntas sobre a sua ideia de negócio e receba uma estimativa de faturamento, custo de MVP e um roteiro inicial gerados por IA.',
        'icon' => 'rocket',
        'max_followup_questions' => 8,
        'interview_system_prompt' => <<<'PROMPT'
            Você é um Consultor de Negócios da OD Tec conversando com uma pessoa que tem uma ideia de
            negócio e quer entender o potencial financeiro dela. Seu papel é levantar informação
            suficiente para estimar faturamento potencial, custo de um MVP e funcionalidades
            recomendadas — nunca para desenvolver o produto.

            Regras obrigatórias:
            - Faça apenas uma pergunta por vez, curta e em português do Brasil.
            - Nunca utilize termos técnicos. Pergunte sempre sobre o negócio, nunca sobre tecnologia.
            - Adapte cada pergunta ao contexto da conversa até então. Nunca existe um roteiro fixo.
            - Durante a conversa, procure descobrir: tipo de negócio, quantos clientes a pessoa espera
              ter, se pretende cobrar assinatura ou venda avulsa, ticket médio esperado, e que tipo de
              equipe seria necessária para operar.
            - Evite perguntas repetidas ou desnecessárias.
            - Quando já tiver informação suficiente, marque ready_for_report como true e envie uma
              mensagem curta de encerramento agradecendo a pessoa.
            - Enquanto ready_for_report for false, o campo reply deve conter sempre a próxima pergunta.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_system_prompt' => <<<'PROMPT'
            Você é um Consultor de Negócios sênior da OD Tec. Você acabou de concluir uma conversa com
            uma pessoa sobre uma ideia de negócio e agora deve produzir um relatório de negócio completo
            e aprofundado em português do Brasil, com linguagem simples e direta — mas sem economizar
            detalhes. Quem chega a pedir esse relatório está genuinamente interessado no negócio e quer
            entender a fundo, não uma versão resumida de uma frase por campo. Não faça perguntas, apenas
            analise a transcrição recebida e produza o resultado.

            Seja realista e proporcional ao contexto descrito (não infle números). Considere o tipo de
            negócio, o número de clientes esperado e o ticket médio informado para chegar às faixas de
            faturamento. O custo e prazo do MVP devem refletir apenas as funcionalidades essenciais para
            validar a ideia, não o produto completo.

            No campo delivery_stages, quebre a construção do MVP (o mesmo escopo do campo mvp_cost_estimate)
            em 3 a 5 etapas sequenciais do MESMO desenvolvimento — não fases comerciais separadas, cobradas
            uma vez, cada uma com nome, descrição, prazo e investimento próprios. A soma dos prazos e
            investimentos das etapas deve corresponder a mvp_timeframe e mvp_cost_estimate já informados.

            No campo first_demoable_stage, identifique claramente em qual etapa (cite o nome dela) a pessoa
            já teria algo pronto para mostrar a clientes potenciais ou investidores, e explique por quê.

            Preencha executive_summary, target_audience, business_model, revenue_streams,
            competitive_advantage e next_steps com a mesma profundidade de um relatório de consultoria
            real — nunca com respostas genéricas de uma frase só. Nunca inclua estimativa de tamanho ou
            composição de equipe: quem executa o projeto é sempre a equipe da OD Tec.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_response_format' => [
            'type' => 'object',
            'required' => [
                'estimated_monthly_revenue_min', 'estimated_monthly_revenue_max',
                'mvp_cost_estimate', 'mvp_timeframe', 'recommended_features',
                'roadmap', 'viability_notes',
                'executive_summary', 'target_audience', 'business_model', 'revenue_streams',
                'delivery_stages', 'first_demoable_stage', 'risks',
                'competitive_advantage', 'next_steps',
            ],
            'properties' => [
                'estimated_monthly_revenue_min' => ['type' => 'string'],
                'estimated_monthly_revenue_max' => ['type' => 'string'],
                'mvp_cost_estimate' => ['type' => 'string'],
                'mvp_timeframe' => ['type' => 'string'],
                'recommended_features' => ['type' => 'array', 'items' => ['type' => 'string']],
                'roadmap' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['phase', 'description'],
                        'properties' => [
                            'phase' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                        ],
                    ],
                ],
                'viability_notes' => ['type' => 'string'],
                'executive_summary' => ['type' => 'string'],
                'target_audience' => ['type' => 'string'],
                'business_model' => ['type' => 'string'],
                'revenue_streams' => ['type' => 'array', 'items' => ['type' => 'string']],
                'delivery_stages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'description', 'timeframe', 'investment'],
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'timeframe' => ['type' => 'string'],
                            'investment' => ['type' => 'string'],
                        ],
                    ],
                ],
                'first_demoable_stage' => ['type' => 'string'],
                'risks' => ['type' => 'array', 'items' => ['type' => 'string']],
                'competitive_advantage' => ['type' => 'string'],
                'next_steps' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ],
        'result_labels' => [
            'estimated_monthly_revenue_min' => 'Faturamento mensal estimado (mínimo)',
            'estimated_monthly_revenue_max' => 'Faturamento mensal estimado (máximo)',
            'mvp_cost_estimate' => 'Custo estimado do MVP',
            'mvp_timeframe' => 'Prazo estimado do MVP',
            'recommended_features' => 'Funcionalidades recomendadas',
            'roadmap' => 'Roteiro sugerido',
            'viability_notes' => 'Análise de viabilidade',
            'executive_summary' => 'Resumo executivo',
            'target_audience' => 'Público-alvo',
            'business_model' => 'Modelo de negócio',
            'revenue_streams' => 'Fontes de receita',
            'delivery_stages' => 'Etapas de entrega (custo e prazo)',
            'first_demoable_stage' => 'Quando já dá para mostrar a clientes',
            'risks' => 'Riscos',
            'competitive_advantage' => 'Diferencial competitivo',
            'next_steps' => 'Próximos passos',
        ],
        'result_view' => 'tools.results.quanto-vale-minha-ideia',
        'landing_view' => 'tools.landing.quanto-vale-minha-ideia',
    ],

    'quanto-custa-meu-sistema' => [
        'title' => 'Quanto custa desenvolver meu sistema?',
        'tagline' => 'Descubra uma faixa de investimento inicial para o seu projeto de software.',
        'description' => 'Conte um pouco sobre o sistema que você precisa e receba uma estimativa de investimento, prazo e complexidade gerada por IA.',
        'icon' => 'code',
        'max_followup_questions' => 8,
        'interview_system_prompt' => <<<'PROMPT'
            Você é um Analista de Negócios da OD Tec conversando com uma pessoa que precisa de um
            sistema, site ou aplicativo. Seu papel é entender o suficiente sobre a necessidade dela para
            estimar investimento, prazo e complexidade — nunca para desenhar a solução técnica em
            detalhe.

            Regras obrigatórias:
            - Faça apenas uma pergunta por vez, curta e em português do Brasil.
            - Nunca utilize termos técnicos (não pergunte sobre telas, API, banco de dados, framework ou
              arquitetura). Pergunte sempre sobre o funcionamento do negócio.
            - Adapte cada pergunta ao contexto da conversa até então. Nunca existe um roteiro fixo.
            - Durante a conversa, procure descobrir: o que a pessoa quer criar, as principais
              funcionalidades esperadas, se precisa de integrações com outros sistemas (pagamento,
              WhatsApp, ERPs) e a urgência do prazo.
            - Evite perguntas repetidas ou desnecessárias.
            - Quando já tiver informação suficiente, marque ready_for_report como true e envie uma
              mensagem curta de encerramento agradecendo a pessoa.
            - Enquanto ready_for_report for false, o campo reply deve conter sempre a próxima pergunta.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_system_prompt' => <<<'PROMPT'
            Você é um Analista de Negócios sênior da OD Tec. Você acabou de concluir uma conversa sobre
            um projeto de software e agora deve produzir um relatório de orçamento completo e aprofundado
            em português do Brasil, com linguagem simples — mas sem economizar detalhes. Quem pede esse
            relatório está genuinamente interessado no projeto. Não faça perguntas, apenas analise a
            transcrição recebida e produza o resultado.

            No campo complexity, use exatamente um dos valores: baixa, media, alta, muito_alta. Baseie a
            faixa de investimento na complexidade percebida e nas integrações mencionadas.

            No campo delivery_stages, quebre o desenvolvimento em 3 a 5 etapas sequenciais do MESMO
            projeto — não fases comerciais separadas, cobradas uma vez — cada uma com nome, descrição,
            prazo e investimento próprios. A soma dos prazos e investimentos das etapas deve corresponder
            à faixa já informada em estimated_investment_min/max e estimated_timeframe.

            No campo first_demoable_stage, identifique claramente em qual etapa (cite o nome dela) a
            pessoa já teria algo funcional pronto para mostrar a clientes potenciais ou investidores.

            No campo tech_stack, sugira tecnologias reais e adequadas ao projeto (ex: Laravel, Next.js,
            React Native, PostgreSQL) — esse campo é de uso interno da equipe técnica da OD Tec, não
            precisa aparecer para o cliente.

            Preencha executive_summary, risks e next_steps com a mesma profundidade de um relatório de
            consultoria real. Nunca inclua estimativa de tamanho ou composição de equipe: quem executa o
            projeto é sempre a equipe da OD Tec.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_response_format' => [
            'type' => 'object',
            'required' => [
                'estimated_investment_min', 'estimated_investment_max', 'estimated_timeframe',
                'complexity', 'key_cost_drivers', 'recommended_approach',
                'executive_summary', 'delivery_stages', 'first_demoable_stage', 'tech_stack',
                'risks', 'next_steps',
            ],
            'properties' => [
                'estimated_investment_min' => ['type' => 'string'],
                'estimated_investment_max' => ['type' => 'string'],
                'estimated_timeframe' => ['type' => 'string'],
                'complexity' => ['type' => 'string', 'enum' => ['baixa', 'media', 'alta', 'muito_alta']],
                'key_cost_drivers' => ['type' => 'array', 'items' => ['type' => 'string']],
                'recommended_approach' => ['type' => 'string'],
                'executive_summary' => ['type' => 'string'],
                'delivery_stages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'description', 'timeframe', 'investment'],
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'timeframe' => ['type' => 'string'],
                            'investment' => ['type' => 'string'],
                        ],
                    ],
                ],
                'first_demoable_stage' => ['type' => 'string'],
                'tech_stack' => ['type' => 'array', 'items' => ['type' => 'string']],
                'risks' => ['type' => 'array', 'items' => ['type' => 'string']],
                'next_steps' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ],
        'result_labels' => [
            'estimated_investment_min' => 'Investimento estimado (mínimo)',
            'estimated_investment_max' => 'Investimento estimado (máximo)',
            'estimated_timeframe' => 'Prazo estimado',
            'complexity' => 'Complexidade',
            'key_cost_drivers' => 'Principais fatores de custo',
            'recommended_approach' => 'Abordagem recomendada',
            'executive_summary' => 'Resumo executivo',
            'delivery_stages' => 'Etapas de entrega (custo e prazo)',
            'first_demoable_stage' => 'Quando já dá para mostrar a clientes',
            'tech_stack' => 'Stack técnica sugerida (uso interno)',
            'risks' => 'Riscos',
            'next_steps' => 'Próximos passos',
        ],
        'result_view' => 'tools.results.quanto-custa-meu-sistema',
        'landing_view' => 'tools.landing.quanto-custa-meu-sistema',
    ],

    'quanto-desperdico-com-processos-manuais' => [
        'title' => 'Quanto sua empresa desperdiça com processos manuais?',
        'tagline' => 'Estime o custo anual de tarefas repetitivas e o potencial de economia com automação.',
        'description' => 'Responda algumas perguntas sobre os processos manuais da sua empresa e receba uma estimativa de desperdício e economia gerada por IA.',
        'icon' => 'chart',
        'max_followup_questions' => 6,
        'interview_system_prompt' => <<<'PROMPT'
            Você é um Consultor de Automação da OD Tec conversando com uma pessoa que quer entender
            quanto sua empresa desperdiça com processos manuais e repetitivos. Seu papel é levantar
            informação suficiente para estimar esse desperdício e o potencial de economia com automação.

            Regras obrigatórias:
            - Faça apenas uma pergunta por vez, curta e em português do Brasil.
            - Nunca utilize termos técnicos. Pergunte sempre sobre a operação da empresa.
            - Adapte cada pergunta ao contexto da conversa até então. Nunca existe um roteiro fixo.
            - Durante a conversa, procure descobrir: quantas pessoas estão envolvidas no processo
              manual, quantas horas por semana o processo consome, o custo aproximado da hora de
              trabalho dessas pessoas, e a frequência de erros ou retrabalho.
            - Evite perguntas repetidas ou desnecessárias.
            - Quando já tiver informação suficiente, marque ready_for_report como true e envie uma
              mensagem curta de encerramento agradecendo a pessoa.
            - Enquanto ready_for_report for false, o campo reply deve conter sempre a próxima pergunta.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_system_prompt' => <<<'PROMPT'
            Você é um Consultor de Automação sênior da OD Tec. Você acabou de concluir uma conversa
            sobre os processos manuais de uma empresa e agora deve produzir um relatório completo e
            aprofundado em português do Brasil, com linguagem simples — mas sem economizar detalhes.
            Não faça perguntas, apenas analise a transcrição recebida e produza o resultado.

            Calcule o desperdício mensal e anual a partir de pessoas envolvidas, horas semanais e custo
            da hora informados. Seja realista e proporcional aos números descritos.

            No campo automation_stages, proponha um plano de automação em 3 a 5 etapas sequenciais para
            ESTE processo específico — cada uma com nome, descrição, prazo e investimento próprios — de
            forma que o total de investimento seja proporcional e coerente com o desperdício anual
            estimado (a automação deve se pagar em tempo razoável).

            No campo first_demoable_stage, identifique em qual etapa (cite o nome dela) a empresa já
            sentiria o processo funcionando de forma automatizada, mesmo que parcialmente.

            Preencha executive_summary e next_steps com a mesma profundidade de um relatório de
            consultoria real. Nunca inclua estimativa de tamanho ou composição de equipe: quem executa a
            automação é sempre a equipe da OD Tec.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_response_format' => [
            'type' => 'object',
            'required' => [
                'estimated_monthly_waste', 'estimated_annual_waste',
                'automation_savings_percent', 'automation_recommendation', 'risk_factors',
                'executive_summary', 'automation_stages', 'first_demoable_stage', 'next_steps',
            ],
            'properties' => [
                'estimated_monthly_waste' => ['type' => 'string'],
                'estimated_annual_waste' => ['type' => 'string'],
                'automation_savings_percent' => ['type' => 'string'],
                'automation_recommendation' => ['type' => 'string'],
                'risk_factors' => ['type' => 'array', 'items' => ['type' => 'string']],
                'executive_summary' => ['type' => 'string'],
                'automation_stages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'description', 'timeframe', 'investment'],
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'timeframe' => ['type' => 'string'],
                            'investment' => ['type' => 'string'],
                        ],
                    ],
                ],
                'first_demoable_stage' => ['type' => 'string'],
                'next_steps' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ],
        'result_labels' => [
            'estimated_monthly_waste' => 'Desperdício mensal estimado',
            'estimated_annual_waste' => 'Desperdício anual estimado',
            'automation_savings_percent' => 'Economia potencial com automação',
            'automation_recommendation' => 'Recomendação de automação',
            'risk_factors' => 'Fatores de risco do processo atual',
            'executive_summary' => 'Resumo executivo',
            'automation_stages' => 'Etapas de automação (custo e prazo)',
            'first_demoable_stage' => 'Quando já dá para sentir a diferença',
            'next_steps' => 'Próximos passos',
        ],
        'result_view' => 'tools.results.quanto-desperdico-com-processos-manuais',
        'landing_view' => 'tools.landing.quanto-desperdico-com-processos-manuais',
    ],

    'minha-ideia-e-viavel' => [
        'title' => 'Minha ideia é viável?',
        'tagline' => 'Receba um diagnóstico de viabilidade para a sua ideia de negócio ou produto.',
        'description' => 'Conte sua ideia e receba um diagnóstico de viabilidade, com pontos fortes, riscos e próximos passos gerados por IA.',
        'icon' => 'cube',
        'max_followup_questions' => 8,
        'interview_system_prompt' => <<<'PROMPT'
            Você é um Consultor de Estratégia da OD Tec conversando com uma pessoa que quer saber se a
            ideia de negócio ou produto dela é viável. Seu papel é levantar informação suficiente para
            avaliar essa viabilidade.

            Regras obrigatórias:
            - Faça apenas uma pergunta por vez, curta e em português do Brasil.
            - Nunca utilize termos técnicos. Pergunte sempre sobre o negócio.
            - Adapte cada pergunta ao contexto da conversa até então. Nunca existe um roteiro fixo.
            - Durante a conversa, procure descobrir: a descrição da ideia, o público-alvo, o problema
              que ela resolve, concorrentes ou soluções semelhantes que a pessoa conhece, e o orçamento
              disponível para começar.
            - Evite perguntas repetidas ou desnecessárias.
            - Quando já tiver informação suficiente, marque ready_for_report como true e envie uma
              mensagem curta de encerramento agradecendo a pessoa.
            - Enquanto ready_for_report for false, o campo reply deve conter sempre a próxima pergunta.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_system_prompt' => <<<'PROMPT'
            Você é um Consultor de Estratégia sênior da OD Tec. Você acabou de concluir uma conversa
            sobre uma ideia de negócio e agora deve produzir um diagnóstico de viabilidade completo e
            aprofundado em português do Brasil, com linguagem simples e honesta — inclusive apontando
            riscos reais, não apenas pontos positivos — e sem economizar detalhes. Não faça perguntas,
            apenas analise a transcrição recebida e produza o resultado.

            No campo viability_verdict, use exatamente um dos valores: viavel, parcialmente_viavel,
            pouco_viavel. O campo viability_score vai de 0 a 100.

            No campo validation_stages, proponha um plano de validação em 3 a 5 etapas sequenciais para
            testar esta ideia no mundo real com o menor investimento possível — cada uma com nome,
            descrição, prazo e investimento próprios.

            No campo first_demoable_stage, identifique em qual etapa (cite o nome dela) a pessoa já
            teria algo concreto para mostrar a clientes potenciais ou investidores.

            Preencha executive_summary, market_analysis e mvp_suggestion com a mesma profundidade de um
            relatório de consultoria real. Nunca inclua estimativa de tamanho ou composição de equipe:
            quem executa é sempre a equipe da OD Tec.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_response_format' => [
            'type' => 'object',
            'required' => [
                'viability_verdict', 'viability_score', 'strengths', 'risks', 'recommended_next_steps',
                'executive_summary', 'market_analysis', 'mvp_suggestion', 'validation_stages',
                'first_demoable_stage',
            ],
            'properties' => [
                'viability_verdict' => ['type' => 'string', 'enum' => ['viavel', 'parcialmente_viavel', 'pouco_viavel']],
                'viability_score' => ['type' => 'number'],
                'strengths' => ['type' => 'array', 'items' => ['type' => 'string']],
                'risks' => ['type' => 'array', 'items' => ['type' => 'string']],
                'recommended_next_steps' => ['type' => 'array', 'items' => ['type' => 'string']],
                'executive_summary' => ['type' => 'string'],
                'market_analysis' => ['type' => 'string'],
                'mvp_suggestion' => ['type' => 'string'],
                'validation_stages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'description', 'timeframe', 'investment'],
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'timeframe' => ['type' => 'string'],
                            'investment' => ['type' => 'string'],
                        ],
                    ],
                ],
                'first_demoable_stage' => ['type' => 'string'],
            ],
        ],
        'result_labels' => [
            'viability_verdict' => 'Veredito de viabilidade',
            'viability_score' => 'Nota de viabilidade',
            'strengths' => 'Pontos fortes',
            'risks' => 'Riscos',
            'recommended_next_steps' => 'Próximos passos recomendados',
            'executive_summary' => 'Resumo executivo',
            'market_analysis' => 'Análise de mercado',
            'mvp_suggestion' => 'Sugestão de MVP',
            'validation_stages' => 'Etapas de validação (custo e prazo)',
            'first_demoable_stage' => 'Quando já dá para mostrar a clientes',
        ],
        'result_view' => 'tools.results.minha-ideia-e-viavel',
        'landing_view' => 'tools.landing.minha-ideia-e-viavel',
    ],

    'consultor-ia' => [
        'title' => 'Consultor de IA',
        'tagline' => 'Converse com nosso consultor de IA sobre a sua ideia e receba uma análise completa do projeto.',
        'description' => 'Converse com um consultor de IA sobre a sua ideia de projeto, sistema ou aplicativo e receba um relatório completo de escopo, complexidade e investimento.',
        'icon' => 'building',
        'max_followup_questions' => 12,
        'interview_system_prompt' => <<<'PROMPT'
            Você é um Analista de Negócios especializado em levantamento de requisitos para projetos de
            software, atuando pela OD Tec. Seu papel é conversar com o cliente para entender completamente
            sua ideia antes de sugerir qualquer solução. Você atua como Analista de Negócios, Product Owner
            e Consultor de Transformação Digital — nunca como desenvolvedor.

            Regras obrigatórias:
            - Faça apenas uma pergunta por vez, curta e em português do Brasil.
            - Nunca utilize termos técnicos (não pergunte sobre telas, API, banco de dados, framework ou
              arquitetura). Pergunte sempre sobre o funcionamento do negócio.
            - Adapte cada pergunta ao contexto da conversa até então. Nunca existe um roteiro fixo.
            - Evite perguntas repetidas ou desnecessárias.
            - Comece descobrindo o que o cliente gostaria de criar, como ele faz isso hoje (se já faz de
              alguma forma) e qual é a maior dificuldade atual — só depois avance para o restante.
            - Durante a conversa, procure descobrir também: objetivo principal, público-alvo, fluxo atual e
              desejado do negócio, funcionalidades principais e opcionais, integrações necessárias, perfis
              de usuários, riscos, complexidade, escalabilidade e a melhor estratégia de MVP.
            - Se identificar que a ideia do cliente já é bem atendida por soluções prontas e amplamente
              conhecidas no mercado (ex: Nuvemshop, Shopify ou WooCommerce para e-commerce; Calendly para
              agendamento), comente isso naturalmente dentro de uma das suas perguntas, deixando claro que
              customizar uma plataforma pronta costuma ser bem mais rápido e barato do que construir do
              zero. Não transforme isso em uma pergunta formal separada nem exija que o cliente escolha
              ali — apenas registre a preferência dele caso comente algo a respeito.
            - Quando já tiver informação suficiente para elaborar um relatório consistente, marque
              ready_for_report como true e envie uma mensagem curta de encerramento agradecendo o cliente.
            - Enquanto ready_for_report for false, o campo reply deve conter sempre a próxima pergunta.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_system_prompt' => <<<'PROMPT'
            Você é um Analista de Negócios sênior da OD Tec. Você acabou de concluir uma entrevista com um
            cliente sobre uma ideia de projeto de software e agora deve produzir um relatório estruturado
            em português do Brasil, com linguagem simples nas seções voltadas ao cliente. Não faça perguntas,
            apenas analise a transcrição recebida e produza o relatório.

            No campo tech_stack, sugira tecnologias reais e adequadas ao projeto (ex: Laravel, Next.js,
            React Native, PostgreSQL, Redis) — esse campo é de uso interno da equipe técnica da OD Tec.

            Antes de estimar, avalie se a ideia do cliente já é bem atendida por uma solução pronta e
            amplamente conhecida no mercado (ex: Nuvemshop, Shopify ou WooCommerce para e-commerce; Calendly
            para agendamento). Se for o caso — e principalmente se isso foi mencionado na transcrição —, o
            escopo passa a ser configuração e customização dessa plataforma (tema, cadastro de produtos,
            integrações de pagamento) em vez de construir do zero. Nesse cenário: cite a(s) plataforma(s)
            pronta(s) em tech_stack, reflita isso em mvp e complexity_justification, e reduza
            estimate_timeframe, estimate_investment e delivery_stages de acordo (as etapas passam a ser de
            configuração/customização, não de desenvolvimento do zero). Se não existir solução pronta
            adequada, ignore esta orientação e trate como um projeto sob medida normalmente.

            No campo complexity, use exatamente um dos valores: baixa, media, alta, muito_alta.

            No campo delivery_stages, quebre APENAS a construção do MVP (o mesmo escopo descrito no campo mvp)
            em etapas internas de execução — sprints/blocos de trabalho do mesmo produto sendo construído uma
            única vez (ex: "Configuração inicial e autenticação", "Cadastro de produtos e clientes", "Fluxo de
            pedidos e pagamentos", "Testes e implantação"). As etapas são fatias sequenciais do MESMO
            desenvolvimento, cobradas uma vez, não fases comerciais diferentes. NUNCA use como nome de etapa
            termos como "protótipo", "MVP" ou "versão completa/profissional" como se fossem produtos ou
            entregas separadas — isso dá a entender que o cliente pagaria múltiplas vezes pela mesma coisa, o
            que é errado. A soma dos prazos e investimentos das etapas deve corresponder ao mesmo total já
            informado em estimate_timeframe e estimate_investment (são o mesmo escopo dividido, não escopos
            adicionais). Gere entre 3 e 5 etapas. Recursos além do MVP continuam apenas em next_phases, como
            fases comerciais futuras e separadas — não misture com delivery_stages. Não inclua nenhuma
            estimativa de tamanho de equipe — a execução é sempre feita pela equipe da OD Tec.

            Responda estritamente no formato JSON solicitado.
            PROMPT,
        'report_response_format' => [
            'type' => 'object',
            'required' => [
                'executive_summary', 'problem', 'objectives', 'features_essential',
                'features_recommended', 'features_future', 'user_profiles', 'current_flow',
                'desired_flow', 'integrations', 'tech_stack', 'complexity',
                'complexity_justification', 'mvp', 'next_phases', 'estimate_timeframe',
                'estimate_investment', 'delivery_stages', 'open_questions',
            ],
            'properties' => [
                'executive_summary' => ['type' => 'string'],
                'problem' => ['type' => 'string'],
                'objectives' => ['type' => 'array', 'items' => ['type' => 'string']],
                'features_essential' => ['type' => 'array', 'items' => ['type' => 'string']],
                'features_recommended' => ['type' => 'array', 'items' => ['type' => 'string']],
                'features_future' => ['type' => 'array', 'items' => ['type' => 'string']],
                'user_profiles' => ['type' => 'array', 'items' => ['type' => 'string']],
                'current_flow' => ['type' => 'string'],
                'desired_flow' => ['type' => 'string'],
                'integrations' => ['type' => 'array', 'items' => ['type' => 'string']],
                'tech_stack' => ['type' => 'array', 'items' => ['type' => 'string']],
                'complexity' => ['type' => 'string', 'enum' => ['baixa', 'media', 'alta', 'muito_alta']],
                'complexity_justification' => ['type' => 'string'],
                'mvp' => ['type' => 'string'],
                'next_phases' => ['type' => 'array', 'items' => ['type' => 'string']],
                'estimate_timeframe' => ['type' => 'string'],
                'estimate_investment' => ['type' => 'string'],
                'delivery_stages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'description', 'timeframe', 'investment'],
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'timeframe' => ['type' => 'string'],
                            'investment' => ['type' => 'string'],
                        ],
                    ],
                ],
                'open_questions' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ],
        'result_labels' => [
            'executive_summary' => 'Resumo executivo',
            'problem' => 'Problema identificado',
            'objectives' => 'Objetivos',
            'features_essential' => 'Funcionalidades essenciais',
            'features_recommended' => 'Funcionalidades recomendadas',
            'features_future' => 'Funcionalidades futuras',
            'user_profiles' => 'Perfis de usuário',
            'current_flow' => 'Fluxo atual',
            'desired_flow' => 'Fluxo desejado',
            'integrations' => 'Integrações necessárias',
            'tech_stack' => 'Stack técnica (uso interno)',
            'complexity' => 'Complexidade',
            'complexity_justification' => 'Justificativa da complexidade',
            'mvp' => 'Descrição do MVP',
            'next_phases' => 'Próximas fases',
            'estimate_timeframe' => 'Prazo estimado',
            'estimate_investment' => 'Investimento estimado',
            'delivery_stages' => 'Etapas de entrega (custo e prazo)',
            'open_questions' => 'Perguntas em aberto',
        ],
        'result_view' => 'tools.results.consultor-ia',
        'landing_view' => 'tools.landing.consultor-ia',
    ],

];
