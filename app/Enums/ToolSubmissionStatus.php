<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ToolSubmissionStatus: string implements HasColor, HasLabel
{
    case InProgress = 'in_progress';
    case GeneratingReport = 'generating_report';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::InProgress => 'Em andamento',
            self::GeneratingReport => 'Gerando resultado',
            self::Completed => 'Concluída',
            self::Failed => 'Falhou',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InProgress => 'gray',
            self::GeneratingReport => 'warning',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
