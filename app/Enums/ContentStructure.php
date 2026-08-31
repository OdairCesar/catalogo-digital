<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentStructure: string implements HasLabel
{
    case Faq = 'faq';
    case Table = 'table';
    case ListFormat = 'list';
    case StepByStep = 'step_by_step';
    case Checklist = 'checklist';
    case CaseStudies = 'case_studies';
    case Examples = 'examples';
    case Comparisons = 'comparisons';

    public function getLabel(): string
    {
        return match ($this) {
            self::Faq => 'FAQ',
            self::Table => 'Tabela',
            self::ListFormat => 'Lista',
            self::StepByStep => 'Passo a passo',
            self::Checklist => 'Checklist',
            self::CaseStudies => 'Estudos de caso',
            self::Examples => 'Exemplos',
            self::Comparisons => 'Comparações',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::Faq => 'uma seção de perguntas frequentes (FAQ)',
            self::Table => 'uma tabela',
            self::ListFormat => 'uma lista',
            self::StepByStep => 'um passo a passo',
            self::Checklist => 'um checklist',
            self::CaseStudies => 'estudos de caso',
            self::Examples => 'exemplos práticos',
            self::Comparisons => 'comparações',
        };
    }
}
