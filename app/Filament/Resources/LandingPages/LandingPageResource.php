<?php

namespace App\Filament\Resources\LandingPages;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\LandingPages\Pages\CreateLandingPage;
use App\Filament\Resources\LandingPages\Pages\EditLandingPage;
use App\Filament\Resources\LandingPages\Pages\ListLandingPages;
use App\Filament\Resources\LandingPages\Schemas\LandingPageForm;
use App\Filament\Resources\LandingPages\Tables\LandingPagesTable;
use App\Models\LandingPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LandingPageResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = LandingPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Paginas;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'landing page';

    protected static ?string $pluralModelLabel = 'landing pages';

    public static function form(Schema $schema): Schema
    {
        return LandingPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LandingPagesTable::configure($table);
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
            'index' => ListLandingPages::route('/'),
            'create' => CreateLandingPage::route('/create'),
            'edit' => EditLandingPage::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Servicos;
    }
}
