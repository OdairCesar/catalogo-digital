<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\ExtraFieldsRepeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->label('Depoimento')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('data.author_name')
                    ->label('Nome')
                    ->placeholder('Ex: Marina, 27')
                    ->required(),
                TextInput::make('data.author_detail')
                    ->label('Detalhe')
                    ->placeholder('Ex: veste 44'),
                Select::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->placeholder('Nenhum — avaliação da loja')
                    ->columnSpanFull()
                    ->helperText('Se preenchido, o depoimento aparece na página desse produto. Se vazio (\"Nenhum\"), aparece na home como avaliação da loja.'),
                Select::make('status')
                    ->label('Status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Published)
                    ->required(),
                ExtraFieldsRepeater::make(),
            ]);
    }
}
