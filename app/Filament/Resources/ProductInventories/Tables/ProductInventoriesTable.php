<?php

namespace App\Filament\Resources\ProductInventories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductInventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('Loja')
                    ->searchable(),
                TextColumn::make('product.title')
                    ->label('Produto')
                    ->searchable(),
                TextColumn::make('productVariant.sku')
                    ->label('Variação')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL'),
                TextColumn::make('quantity')
                    ->label('Estoque'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('store_id')
                    ->label('Loja')
                    ->relationship('store', 'name')
                    ->searchable(),
                SelectFilter::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'title')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
