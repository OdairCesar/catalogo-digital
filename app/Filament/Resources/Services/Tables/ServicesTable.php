<?php

namespace App\Filament\Resources\Services\Tables;

use App\Enums\PageStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\GenerateImageAction;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Service;
use App\Services\Landing\ServiceHeroImageGenerator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServicesTable
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
                TextColumn::make('subtitle')
                    ->label('Subtítulo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('hero_image')
                    ->label('Imagem')
                    ->disk('cloudinary')
                    ->checkFileExistence(false),
                TextColumn::make('landing_pages_count')
                    ->label('Landing pages')
                    ->counts('landingPages'),
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
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PageStatus::class),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn (Service $record): string => route('services.show', $record),
                    visible: fn (Service $record): bool => $record->status === PageStatus::Published,
                ),
                GenerateImageAction::make('hero_image', fn (Service $record): string => app(ServiceHeroImageGenerator::class)->generate(
                    title: $record->title,
                    subtitle: $record->subtitle,
                    description: $record->description,
                    benefits: $record->benefits,
                )),
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
