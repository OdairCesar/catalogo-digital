<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WritingTone: string implements HasLabel
{
    case Professional = 'professional';
    case Technical = 'technical';
    case Conversational = 'conversational';
    case Consultive = 'consultive';
    case Salesy = 'salesy';
    case Institutional = 'institutional';

    public function getLabel(): string
    {
        return match ($this) {
            self::Professional => 'Profissional',
            self::Technical => 'Técnico',
            self::Conversational => 'Conversacional',
            self::Consultive => 'Consultivo',
            self::Salesy => 'Vendedor',
            self::Institutional => 'Institucional',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::Professional => 'Use um tom profissional.',
            self::Technical => 'Use um tom técnico, preciso e direto.',
            self::Conversational => 'Use um tom conversacional, como se estivesse batendo um papo com o leitor.',
            self::Consultive => 'Use um tom consultivo, como um especialista orientando o leitor.',
            self::Salesy => 'Use um tom vendedor, persuasivo, conduzindo o leitor a uma ação.',
            self::Institutional => 'Use um tom institucional, formal e alinhado à voz de uma empresa.',
        };
    }
}
