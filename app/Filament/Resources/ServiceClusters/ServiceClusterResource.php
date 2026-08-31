<?php

namespace App\Filament\Resources\ServiceClusters;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\ServiceClusters\Pages\CreateServiceCluster;
use App\Filament\Resources\ServiceClusters\Pages\EditServiceCluster;
use App\Filament\Resources\ServiceClusters\Pages\GenerateAiServiceCluster;
use App\Filament\Resources\ServiceClusters\Pages\ListServiceClusters;
use App\Filament\Resources\ServiceClusters\Schemas\ServiceClusterForm;
use App\Filament\Resources\ServiceClusters\Tables\ServiceClustersTable;
use App\Models\ServiceCluster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServiceClusterResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = ServiceCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Servicos;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'cluster';

    protected static ?string $pluralModelLabel = 'clusters';

    public static function form(Schema $schema): Schema
    {
        return ServiceClusterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceClustersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceClusters::route('/'),
            'create' => CreateServiceCluster::route('/create'),
            'generate' => GenerateAiServiceCluster::route('/generate'),
            'edit' => EditServiceCluster::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Servicos;
    }
}
