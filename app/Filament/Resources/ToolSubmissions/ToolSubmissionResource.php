<?php

namespace App\Filament\Resources\ToolSubmissions;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\ToolSubmissions\Pages\EditToolSubmission;
use App\Filament\Resources\ToolSubmissions\Pages\ListToolSubmissions;
use App\Filament\Resources\ToolSubmissions\Schemas\ToolSubmissionForm;
use App\Filament\Resources\ToolSubmissions\Tables\ToolSubmissionsTable;
use App\Models\ToolSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ToolSubmissionResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = ToolSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Crm;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'submissão de ferramenta';

    protected static ?string $pluralModelLabel = 'submissões de ferramentas';

    public static function form(Schema $schema): Schema
    {
        return ToolSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ToolSubmissionsTable::configure($table);
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
            'index' => ListToolSubmissions::route('/'),
            'edit' => EditToolSubmission::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Ferramentas;
    }
}
