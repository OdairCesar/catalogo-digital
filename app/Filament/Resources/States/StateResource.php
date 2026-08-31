<?php

namespace App\Filament\Resources\States;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\States\Pages\CreateState;
use App\Filament\Resources\States\Pages\EditState;
use App\Filament\Resources\States\Pages\ListStates;
use App\Filament\Resources\States\Schemas\StateForm;
use App\Filament\Resources\States\Tables\StatesTable;
use App\Models\State;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StateResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = State::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Localizacao;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'estado';

    protected static ?string $pluralModelLabel = 'estados';

    public static function form(Schema $schema): Schema
    {
        return StateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatesTable::configure($table);
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
            'index' => ListStates::route('/'),
            'create' => CreateState::route('/create'),
            'edit' => EditState::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Servicos;
    }
}
