<?php

namespace App\Filament\Resources\ServiceClusters\Schemas;

use App\Models\Service;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GenerateAiServiceClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->label('Serviço')
                    ->options(fn (): array => Service::query()->active()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                TextInput::make('topic')
                    ->label('Tópico/palavra-chave do cluster')
                    ->helperText('Ex: "Loja Virtual", "Site para Clínica". A IA usa o contexto do serviço para escrever a página.')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
