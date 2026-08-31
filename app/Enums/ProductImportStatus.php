<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductImportStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Mapping = 'mapping';
    case AwaitingReview = 'awaiting_review';
    case Importing = 'importing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Mapping => 'Analisando planilha...',
            self::AwaitingReview => 'Aguardando revisão',
            self::Importing => 'Importando...',
            self::Completed => 'Concluída',
            self::Failed => 'Falhou',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending, self::Mapping, self::Importing => 'warning',
            self::AwaitingReview => 'info',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
