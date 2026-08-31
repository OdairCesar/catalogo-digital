<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use App\Filament\Support\Forms\LandingPageFormFields;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class LandingPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->required(),
                Select::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->required(),
                ...LandingPageFormFields::common(
                    'Gerado automaticamente como {servico}-em-{cidade}; só altere se souber o que está fazendo.',
                ),
            ]);
    }
}
