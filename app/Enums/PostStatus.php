<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PostStatus: string implements HasColor, HasLabel
{
    case Generating = 'generating';
    case Draft = 'draft';
    case Published = 'published';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Generating => 'Gerando...',
            self::Draft => 'Rascunho',
            self::Published => 'Publicado',
            self::Failed => 'Falhou',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Generating => 'warning',
            self::Draft => 'gray',
            self::Published => 'success',
            self::Failed => 'danger',
        };
    }
}
