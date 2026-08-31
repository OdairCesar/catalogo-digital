<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReaderInterest: string implements HasLabel
{
    case Price = 'price';
    case HowItWorks = 'how_it_works';
    case Advantages = 'advantages';
    case Comparison = 'comparison';
    case StepByStep = 'step_by_step';
    case BestOptions = 'best_options';
    case CommonMistakes = 'common_mistakes';

    public function getLabel(): string
    {
        return match ($this) {
            self::Price => 'Quanto custa',
            self::HowItWorks => 'Como funciona',
            self::Advantages => 'Vantagens',
            self::Comparison => 'Comparação',
            self::StepByStep => 'Passo a passo',
            self::BestOptions => 'Melhores opções',
            self::CommonMistakes => 'Erros comuns',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::Price => 'quanto custa',
            self::HowItWorks => 'como funciona',
            self::Advantages => 'quais são as vantagens',
            self::Comparison => 'uma comparação entre opções',
            self::StepByStep => 'um passo a passo prático',
            self::BestOptions => 'quais são as melhores opções',
            self::CommonMistakes => 'quais são os erros comuns a evitar',
        };
    }
}
