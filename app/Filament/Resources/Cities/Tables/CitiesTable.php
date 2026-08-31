<?php

namespace App\Filament\Resources\Cities\Tables;

use App\Enums\PageStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\City;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('state.name')
                    ->label('Estado')
                    ->searchable(),
                TextColumn::make('state.uf')
                    ->label('UF')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')
                    ->label('Região')
                    ->searchable(),
                TextColumn::make('population')
                    ->label('População')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('landing_pages_count')
                    ->label('Landing pages')
                    ->counts('landingPages'),
                TextColumn::make('gdp')
                    ->label('PIB')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('population', 'desc')
            ->filters([
                SelectFilter::make('state_id')
                    ->label('Estado')
                    ->relationship('state', 'name')
                    ->searchable(),
                SelectFilter::make('region')
                    ->label('Região')
                    ->options(fn (): array => City::query()
                        ->whereNotNull('region')
                        ->distinct()
                        ->orderBy('region')
                        ->pluck('region', 'region')
                        ->all()),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PageStatus::class),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn (City $record): string => route('cities.show', $record),
                    visible: fn (City $record): bool => $record->status === PageStatus::Published,
                    label: 'Ver site',
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
