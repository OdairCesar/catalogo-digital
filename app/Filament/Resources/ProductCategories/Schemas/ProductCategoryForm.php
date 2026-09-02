<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AutoSlug::attach(
                    TextInput::make('name')
                        ->label('Nome')
                        ->required(),
                ),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /produtos/categoria/{slug}.'),
                TextInput::make('google_product_category')
                    ->label('Categoria no Google')
                    ->helperText('Código ou nome da categoria na taxonomia do Google Merchant Center.'),
                Textarea::make('description')
                    ->label('Descrição')
                    ->columnSpanFull(),
                CloudinaryImageUpload::make('image')
                    ->label('Imagem')
                    ->helperText('Exibida na vitrine de categorias do site.')
                    ->columnSpanFull(),
            ]);
    }
}
