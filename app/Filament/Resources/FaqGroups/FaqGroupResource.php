<?php

namespace App\Filament\Resources\FaqGroups;

use App\Enums\NavigationGroup;
use App\Enums\SectionType;
use App\Filament\Concerns\ScopedToSectionType;
use App\Filament\Resources\FaqGroups\Pages\CreateFaqGroup;
use App\Filament\Resources\FaqGroups\Pages\EditFaqGroup;
use App\Filament\Resources\FaqGroups\Pages\ListFaqGroups;
use App\Filament\Resources\FaqGroups\Schemas\FaqGroupForm;
use App\Filament\Resources\FaqGroups\Tables\FaqGroupsTable;
use App\Models\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FaqGroupResource extends Resource
{
    use ScopedToSectionType;

    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Secoes;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'grupo de dúvidas';

    protected static ?string $pluralModelLabel = 'dúvidas frequentes';

    public static function form(Schema $schema): Schema
    {
        return FaqGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqGroupsTable::configure($table);
    }

    public static function sectionType(): SectionType
    {
        return SectionType::FaqGroup;
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
            'index' => ListFaqGroups::route('/'),
            'create' => CreateFaqGroup::route('/create'),
            'edit' => EditFaqGroup::route('/{record}/edit'),
        ];
    }
}
