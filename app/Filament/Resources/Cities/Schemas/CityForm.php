<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\MoneyInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /cidades/{slug} e nas landing pages {servico}-em-{slug}.'),
                AutoSlug::attach(
                    TextInput::make('name')
                        ->required(),
                ),
                Select::make('state_id')
                    ->label('Estado')
                    ->relationship('state', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('region')
                    ->required(),
                TextInput::make('population')
                    ->numeric()
                    ->minValue(0),
                MoneyInput::make('gdp')
                    ->label('PIB'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Textarea::make('intro')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('business_text')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Draft)
                    ->required(),
            ]);
    }
}
