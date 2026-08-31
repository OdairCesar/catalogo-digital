<?php

namespace App\Filament\Resources\ServiceClusterLandingPages;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\ServiceClusterLandingPages\Pages\CreateServiceClusterLandingPage;
use App\Filament\Resources\ServiceClusterLandingPages\Pages\EditServiceClusterLandingPage;
use App\Filament\Resources\ServiceClusterLandingPages\Pages\ListServiceClusterLandingPages;
use App\Filament\Resources\ServiceClusterLandingPages\Schemas\ServiceClusterLandingPageForm;
use App\Filament\Resources\ServiceClusterLandingPages\Tables\ServiceClusterLandingPagesTable;
use App\Models\ServiceClusterLandingPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServiceClusterLandingPageResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = ServiceClusterLandingPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Paginas;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'cluster por cidade';

    protected static ?string $pluralModelLabel = 'clusters por cidade';

    public static function form(Schema $schema): Schema
    {
        return ServiceClusterLandingPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceClusterLandingPagesTable::configure($table);
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
            'index' => ListServiceClusterLandingPages::route('/'),
            'create' => CreateServiceClusterLandingPage::route('/create'),
            'edit' => EditServiceClusterLandingPage::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Servicos;
    }
}
