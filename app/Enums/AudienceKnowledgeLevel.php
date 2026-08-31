<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AudienceKnowledgeLevel: string implements HasLabel
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::Beginner => 'Iniciante',
            self::Intermediate => 'Intermediário',
            self::Advanced => 'Avançado',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::Beginner => 'O leitor é iniciante no assunto: explique conceitos básicos e evite jargão técnico sem explicá-lo.',
            self::Intermediate => 'O leitor tem conhecimento intermediário: pode usar termos técnicos comuns, mas contextualize-os.',
            self::Advanced => 'O leitor é avançado no assunto: pode ser técnico e direto, sem precisar explicar conceitos básicos.',
        };
    }
}
