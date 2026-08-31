<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductGender: string implements HasLabel
{
    case Male = 'male';
    case Female = 'female';
    case Unisex = 'unisex';

    public function getLabel(): string
    {
        return match ($this) {
            self::Male => 'Masculino',
            self::Female => 'Feminino',
            self::Unisex => 'Unissex',
        };
    }
}
