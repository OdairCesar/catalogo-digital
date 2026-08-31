<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Crm = 'crm';
    case Servicos = 'servicos';
    case Produtos = 'produtos';
    case Paginas = 'paginas';
    case Blog = 'blog';
    case Secoes = 'secoes';
    case Localizacao = 'localizacao';
    case Institucional = 'institucional';

    public function getLabel(): string
    {
        return match ($this) {
            self::Crm => 'CRM',
            self::Servicos => 'Serviços',
            self::Produtos => 'Produtos',
            self::Paginas => 'Páginas',
            self::Blog => 'Blog',
            self::Secoes => 'Seções',
            self::Localizacao => 'Localização',
            self::Institucional => 'Institucional',
        };
    }
}
