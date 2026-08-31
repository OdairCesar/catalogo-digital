<?php

namespace App\Filament\Resources\FaqGroups\Tables;

use App\Enums\PageStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título do grupo')
                    ->searchable(),
                TextColumn::make('data.faq')
                    ->label('Perguntas')
                    ->state(fn ($record): int => count($record->data['faq'] ?? []))
                    ->formatStateUsing(fn (int $state): string => "{$state} pergunta(s)"),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PageStatus::class),
            ])
            ->recordActions([
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
