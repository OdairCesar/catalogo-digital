<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductAgeGroup: string implements HasLabel
{
    case Newborn = 'newborn';
    case Infant = 'infant';
    case Toddler = 'toddler';
    case Kids = 'kids';
    case Adult = 'adult';

    public function getLabel(): string
    {
        return match ($this) {
            self::Newborn => 'Recém-nascido',
            self::Infant => 'Bebê',
            self::Toddler => 'Criança pequena',
            self::Kids => 'Infantil',
            self::Adult => 'Adulto',
        };
    }
}
