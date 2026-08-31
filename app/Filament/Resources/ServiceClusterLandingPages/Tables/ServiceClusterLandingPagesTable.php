<?php

namespace App\Filament\Resources\ServiceClusterLandingPages\Tables;

use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Filament\Support\Tables\LandingPageTableColumns;
use App\Models\ServiceClusterLandingPage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Route;

class ServiceClusterLandingPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serviceCluster.title')
                    ->label('Cluster')
                    ->searchable()
                    ->sortable(),
                ...LandingPageTableColumns::common(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('service_cluster_id')
                    ->label('Cluster')
                    ->relationship('serviceCluster', 'title')
                    ->searchable(),
                ...LandingPageTableColumns::commonFilters(),
            ])
            ->recordActions([
                ViewOnLandingAction::make(
                    url: fn (ServiceClusterLandingPage $record) => route('services.clusters.show', [
                        $record->serviceCluster->service,
                        $record->slug,
                    ]),
                    visible: fn (): bool => Route::has('services.clusters.show'),
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
