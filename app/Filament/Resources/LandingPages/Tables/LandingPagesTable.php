<?php

namespace App\Filament\Resources\LandingPages\Tables;

use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Filament\Support\Tables\LandingPageTableColumns;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Route;

class LandingPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.name')
                    ->label('Serviço')
                    ->searchable()
                    ->sortable(),
                ...LandingPageTableColumns::common(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('service_id')
                    ->label('Serviço')
                    ->relationship('service', 'name')
                    ->searchable(),
                ...LandingPageTableColumns::commonFilters(),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn ($record) => route('landing.show', $record),
                    visible: fn (): bool => Route::has('landing.show'),
                    label: 'Ver site',
                ),
                LandingPageTableColumns::toggleStatusAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...LandingPageTableColumns::bulkStatusActions(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
