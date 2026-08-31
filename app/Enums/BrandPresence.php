<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BrandPresence: string implements HasLabel
{
    case NotMentioned = 'not_mentioned';
    case Subtle = 'subtle';
    case CtaAtEnd = 'cta_at_end';
    case Throughout = 'throughout';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotMentioned => 'Não aparece',
            self::Subtle => 'Discretamente',
            self::CtaAtEnd => 'CTA no final',
            self::Throughout => 'Ao longo do texto',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::NotMentioned => 'Não mencione a Fit By Cae em nenhum momento do texto.',
            self::Subtle => 'Mencione a Fit By Cae apenas discretamente, sem soar como propaganda.',
            self::CtaAtEnd => 'Mencione a Fit By Cae apenas em uma chamada para ação (CTA) ao final do texto.',
            self::Throughout => 'Mencione a Fit By Cae de forma natural ao longo de todo o texto.',
        };
    }
}
