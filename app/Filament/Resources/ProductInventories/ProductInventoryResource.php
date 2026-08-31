<?php

namespace App\Filament\Resources\ProductInventories;

use App\Enums\NavigationGroup;
use App\Enums\SiteModule;
use App\Filament\Concerns\BelongsToModule;
use App\Filament\Resources\ProductInventories\Pages\CreateProductInventory;
use App\Filament\Resources\ProductInventories\Pages\EditProductInventory;
use App\Filament\Resources\ProductInventories\Pages\ListProductInventories;
use App\Filament\Resources\ProductInventories\Schemas\ProductInventoryForm;
use App\Filament\Resources\ProductInventories\Tables\ProductInventoriesTable;
use App\Models\ProductInventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductInventoryResource extends Resource
{
    use BelongsToModule;

    protected static ?string $model = ProductInventory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Produtos;

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'estoque por loja';

    protected static ?string $pluralModelLabel = 'estoques por loja';

    public static function form(Schema $schema): Schema
    {
        return ProductInventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductInventoriesTable::configure($table);
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
            'index' => ListProductInventories::route('/'),
            'create' => CreateProductInventory::route('/create'),
            'edit' => EditProductInventory::route('/{record}/edit'),
        ];
    }

    protected static function module(): SiteModule
    {
        return SiteModule::Produtos;
    }
}
