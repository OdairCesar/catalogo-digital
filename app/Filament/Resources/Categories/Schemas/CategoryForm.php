<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\Forms\AutoSlug;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AutoSlug::attach(
                    TextInput::make('name')
                        ->required(),
                ),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /blog/categoria/{slug}.'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
