<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductCondition: string implements HasColor, HasLabel
{
    case New = 'new';
    case Used = 'used';
    case Refurbished = 'refurbished';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Novo',
            self::Used => 'Usado',
            self::Refurbished => 'Recondicionado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'success',
            self::Used => 'gray',
            self::Refurbished => 'warning',
        };
    }
}
