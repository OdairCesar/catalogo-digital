<?php

namespace App\Enums;

enum SiteModule: string
{
    case Blog = 'blog';
    case Servicos = 'servicos';
    case Produtos = 'produtos';
    case Ferramentas = 'ferramentas';

    public function isEnabled(): bool
    {
        return (bool) config("modules.{$this->value}");
    }
}
