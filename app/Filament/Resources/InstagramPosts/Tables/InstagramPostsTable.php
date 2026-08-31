<?php

namespace App\Filament\Resources\InstagramPosts\Tables;

use App\Enums\PageStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InstagramPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->disk('cloudinary')
                    ->checkFileExistence(false),
                TextColumn::make('content')
                    ->label('Legenda')
                    ->limit(60)
                    ->placeholder('—'),
                TextColumn::make('data.link')
                    ->label('Link')
                    ->limit(40)
                    ->placeholder('—'),
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
