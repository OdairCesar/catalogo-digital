<?php

namespace App\Filament\Resources\InstagramPosts;

use App\Enums\NavigationGroup;
use App\Enums\SectionType;
use App\Filament\Concerns\ScopedToSectionType;
use App\Filament\Resources\InstagramPosts\Pages\CreateInstagramPost;
use App\Filament\Resources\InstagramPosts\Pages\EditInstagramPost;
use App\Filament\Resources\InstagramPosts\Pages\ListInstagramPosts;
use App\Filament\Resources\InstagramPosts\Schemas\InstagramPostForm;
use App\Filament\Resources\InstagramPosts\Tables\InstagramPostsTable;
use App\Models\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InstagramPostResource extends Resource
{
    use ScopedToSectionType;

    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Secoes;

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'post do Instagram';

    protected static ?string $pluralModelLabel = 'Instagram';

    public static function sectionType(): SectionType
    {
        return SectionType::InstagramPost;
    }

    public static function form(Schema $schema): Schema
    {
        return InstagramPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstagramPostsTable::configure($table);
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
            'index' => ListInstagramPosts::route('/'),
            'create' => CreateInstagramPost::route('/create'),
            'edit' => EditInstagramPost::route('/{record}/edit'),
        ];
    }
}
