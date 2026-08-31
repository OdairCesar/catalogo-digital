<?php

namespace App\Filament\Resources\PageBlocks\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * No status column/toggle here: page blocks are always shown on the site
 * (unlike Testimonial/FaqGroup, which can be drafted). `status` stays
 * fillable on `Section` for those other types.
 */
class PageBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Bloco'),
                TextColumn::make('title')
                    ->label('Título')
                    ->placeholder('—')
                    ->limit(50),
            ])
            ->defaultSort('type')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
