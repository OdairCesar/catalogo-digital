<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ImageStyle: string implements HasLabel
{
    case Photorealistic = 'photorealistic';
    case FlatIllustration = 'flat_illustration';
    case Isometric3d = 'isometric_3d';
    case Minimalist = 'minimalist';
    case Corporate = 'corporate';

    public function getLabel(): string
    {
        return match ($this) {
            self::Photorealistic => 'Fotorrealista',
            self::FlatIllustration => 'Ilustração plana (flat design)',
            self::Isometric3d => '3D isométrico',
            self::Minimalist => 'Minimalista',
            self::Corporate => 'Corporativo/tech',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::Photorealistic => 'Photorealistic photography style, natural lighting, high detail.',
            self::FlatIllustration => 'Flat vector illustration style, clean shapes, limited color palette.',
            self::Isometric3d => 'Isometric 3D render style, soft shadows, clean geometric shapes.',
            self::Minimalist => 'Minimalist design, plenty of negative space, simple shapes, muted colors.',
            self::Corporate => 'Modern corporate technology style, clean and professional, blue and neutral tones.',
        };
    }
}
