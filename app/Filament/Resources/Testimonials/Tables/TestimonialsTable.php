<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Enums\PageStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data.author_name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('content')
                    ->label('Depoimento')
                    ->limit(60)
                    ->searchable(),
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
