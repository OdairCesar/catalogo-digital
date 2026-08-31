<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\ProductAttributes\Pages\CreateProductAttribute;
use App\Filament\Resources\ProductAttributes\Pages\EditProductAttribute;
use App\Filament\Resources\ProductAttributes\Pages\ListProductAttributes;
use App\Filament\Resources\ProductAttributes\Schemas\ProductAttributeForm;
use App\Filament\Resources\ProductAttributes\Tables\ProductAttributesTable;
use App\Models\ProductAttribute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductAttributeResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = ProductAttribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Produtos;

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'atributo';

    protected static ?string $pluralModelLabel = 'atributos';

    public static function form(Schema $schema): Schema
    {
        return ProductAttributeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductAttributesTable::configure($table);
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
            'index' => ListProductAttributes::route('/'),
            'create' => CreateProductAttribute::route('/create'),
            'edit' => EditProductAttribute::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Produtos;
    }
}
