<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PageStatus: string implements HasColor, HasLabel
{
    case Published = 'published';
    case Draft = 'draft';

    public function getLabel(): string
    {
        return match ($this) {
            self::Published => 'Publicado',
            self::Draft => 'Rascunho',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Draft => 'gray',
        };
    }
}
