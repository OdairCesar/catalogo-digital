<?php

namespace App\Filament\Resources\States\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\AutoSlug;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /estados/{slug}.'),
                AutoSlug::attach(
                    TextInput::make('name')
                        ->required(),
                ),
                TextInput::make('uf')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->length(2),
                Textarea::make('intro')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('business_text')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('meta_title'),
                TextInput::make('meta_description'),
                TextInput::make('canonical'),
                TextInput::make('robots')
                    ->default('index,follow'),
                Select::make('status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Draft)
                    ->required(),
            ]);
    }
}
