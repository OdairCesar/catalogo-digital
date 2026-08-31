<?php

namespace App\Filament\Resources\InstagramPosts\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use App\Filament\Support\Forms\ExtraFieldsRepeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstagramPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                CloudinaryImageUpload::make('image')
                    ->label('Imagem')
                    ->required(),
                TextInput::make('data.link')
                    ->label('Link do post')
                    ->url()
                    ->placeholder('https://www.instagram.com/p/...'),
                Textarea::make('content')
                    ->label('Legenda')
                    ->rows(2)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Published)
                    ->required(),
                ExtraFieldsRepeater::make(),
            ]);
    }
}
