<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Weekday: string implements HasLabel
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';

    public function getLabel(): string
    {
        return match ($this) {
            self::Monday => 'Segunda-feira',
            self::Tuesday => 'Terça-feira',
            self::Wednesday => 'Quarta-feira',
            self::Thursday => 'Quinta-feira',
            self::Friday => 'Sexta-feira',
            self::Saturday => 'Sábado',
            self::Sunday => 'Domingo',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Monday => 'Seg',
            self::Tuesday => 'Ter',
            self::Wednesday => 'Qua',
            self::Thursday => 'Qui',
            self::Friday => 'Sex',
            self::Saturday => 'Sáb',
            self::Sunday => 'Dom',
        };
    }
}
