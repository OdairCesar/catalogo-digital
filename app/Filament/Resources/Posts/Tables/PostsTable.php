<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->placeholder('Gerando com IA...'),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge(),
                ImageColumn::make('cover_image')
                    ->label('Imagem')
                    ->disk('cloudinary')
                    ->checkFileExistence(false),
                TextColumn::make('author.name')
                    ->label('Autor')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Publicado em')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PostStatus::class),
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable(),
                SelectFilter::make('author')
                    ->label('Autor')
                    ->relationship('author', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn (Post $record): string => route('blog.show', $record),
                    visible: fn (Post $record): bool => $record->status === PostStatus::Published,
                    label: 'Ver post',
                ),
                TogglePublishStatusAction::make(PostStatus::class),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...BulkPublishStatusActions::make(PostStatus::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
