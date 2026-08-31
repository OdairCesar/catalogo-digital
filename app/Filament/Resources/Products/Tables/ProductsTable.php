<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\PageStatus;
use App\Enums\ProductCondition;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Imagem')
                    ->disk('cloudinary')
                    ->checkFileExistence(false),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('base_price')
                    ->label('Preço')
                    ->money('BRL'),
                TextColumn::make('condition')
                    ->label('Condição')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PageStatus::class),
                SelectFilter::make('condition')
                    ->label('Condição')
                    ->options(ProductCondition::class),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn (Product $record): string => route('products.show', $record),
                    visible: fn (Product $record): bool => $record->status === PageStatus::Published
                        && $record->company->status === PageStatus::Published,
                    label: 'Ver produto',
                ),
                TogglePublishStatusAction::make(PageStatus::class),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...BulkPublishStatusActions::make(PageStatus::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
