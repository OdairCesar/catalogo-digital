<?php

namespace App\Filament\Resources\ServiceClusters\Tables;

use App\Enums\ServiceClusterStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\GenerateImageAction;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\ServiceCluster;
use App\Services\ServiceCluster\ServiceClusterHeroImageGenerator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use RuntimeException;

class ServiceClustersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.title')
                    ->label('Serviço')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->placeholder('Gerando com IA...'),
                ImageColumn::make('hero_image')
                    ->label('Imagem')
                    ->disk('cloudinary')
                    ->checkFileExistence(false),
                TextColumn::make('landing_pages_count')
                    ->label('Cidades')
                    ->counts('landingPages'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('service_id')
                    ->label('Serviço')
                    ->relationship('service', 'title')
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ServiceClusterStatus::class),
                TernaryFilter::make('hero_image')
                    ->label('Imagem')
                    ->nullable()
                    ->placeholder('Todos')
                    ->trueLabel('Com imagem')
                    ->falseLabel('Sem imagem'),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn (ServiceCluster $record): string => route('services.clusters.show', [$record->service, $record]),
                    visible: fn (ServiceCluster $record): bool => $record->status === ServiceClusterStatus::Published,
                ),
                GenerateImageAction::make('hero_image', function (ServiceCluster $record): string {
                    if ($record->title === null) {
                        throw new RuntimeException('O cluster ainda não tem título gerado.');
                    }

                    return app(ServiceClusterHeroImageGenerator::class)->generate(
                        service: $record->service,
                        title: $record->title,
                        subtitle: $record->subtitle,
                        description: $record->description,
                        benefits: $record->benefits ?? [],
                    );
                }),
                TogglePublishStatusAction::make(ServiceClusterStatus::class),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...BulkPublishStatusActions::make(ServiceClusterStatus::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
