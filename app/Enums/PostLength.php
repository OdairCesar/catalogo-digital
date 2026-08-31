<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PostLength: string implements HasLabel
{
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case VeryComplete = 'very_complete';

    public function getLabel(): string
    {
        return match ($this) {
            self::Small => 'Pequeno (~800 palavras)',
            self::Medium => 'Médio (~1500 palavras)',
            self::Large => 'Grande (~2500 palavras)',
            self::VeryComplete => 'Muito completo (~4000+ palavras)',
        };
    }

    public function approxWordCount(): int
    {
        return match ($this) {
            self::Small => 800,
            self::Medium => 1500,
            self::Large => 2500,
            self::VeryComplete => 4000,
        };
    }

    public function promptInstruction(): string
    {
        return "Escreva um artigo com aproximadamente {$this->approxWordCount()} palavras.";
    }
}
