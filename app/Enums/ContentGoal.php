<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentGoal: string implements HasLabel
{
    case Educate = 'educate';
    case Sell = 'sell';
    case GenerateLeads = 'generate_leads';
    case CompareSolutions = 'compare_solutions';
    case AnswerQuestions = 'answer_questions';
    case ShowAuthority = 'show_authority';

    public function getLabel(): string
    {
        return match ($this) {
            self::Educate => 'Educar',
            self::Sell => 'Vender',
            self::GenerateLeads => 'Gerar leads',
            self::CompareSolutions => 'Comparar soluções',
            self::AnswerQuestions => 'Tirar dúvidas',
            self::ShowAuthority => 'Mostrar autoridade',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::Educate => 'O objetivo principal é educar o leitor sobre o tema.',
            self::Sell => 'O objetivo principal é vender, conduzindo o leitor a uma decisão de compra.',
            self::GenerateLeads => 'O objetivo principal é gerar leads, incentivando o leitor a entrar em contato.',
            self::CompareSolutions => 'O objetivo principal é comparar soluções disponíveis para o problema do leitor.',
            self::AnswerQuestions => 'O objetivo principal é tirar as dúvidas mais comuns do leitor sobre o tema.',
            self::ShowAuthority => 'O objetivo principal é demonstrar autoridade e experiência no assunto.',
        };
    }
}
