<?php

namespace App\Filament\Resources\PageBlocks;

use App\Enums\NavigationGroup;
use App\Enums\SectionType;
use App\Filament\Resources\PageBlocks\Pages\EditPageBlock;
use App\Filament\Resources\PageBlocks\Pages\ListPageBlocks;
use App\Filament\Resources\PageBlocks\Schemas\PageBlockForm;
use App\Filament\Resources\PageBlocks\Tables\PageBlocksTable;
use App\Models\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Manages the fixed set of singleton page blocks (home hero, trust bar,
 * WhatsApp banner, the About page). These are seeded once by `SectionSeeder`
 * and are only ever edited — never created or deleted — so this resource has
 * no create page and no delete action.
 *
 * The Instagram intro text is also a singleton, but lives in its own
 * InstagramResource alongside the Instagram posts list, not here.
 */
class PageBlockResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Secoes;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'bloco de página';

    protected static ?string $pluralModelLabel = 'blocos de página';

    public static function form(Schema $schema): Schema
    {
        return PageBlockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageBlocksTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('type', SectionType::singletons())
            ->where('type', '!=', SectionType::Instagram);
    }

    public static function canCreate(): bool
    {
        return false;
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
            'index' => ListPageBlocks::route('/'),
            'edit' => EditPageBlock::route('/{record}/edit'),
        ];
    }
}
