<?php

namespace App\Filament\Resources\ProductInventories\Schemas;

use App\Filament\Support\Forms\MoneyInput;
use App\Models\ProductInventory;
use App\Models\ProductVariant;
use App\Models\Store;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->label('Loja')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->rules([
                        fn (Get $get, ?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                            $exists = ProductInventory::query()
                                ->where('store_id', $value)
                                ->where('product_id', $get('product_id'))
                                ->where('product_variant_id', $get('product_variant_id'))
                                ->when($record, fn (Builder $query, Model $record): Builder => $query->whereKeyNot($record->getKey()))
                                ->exists();

                            if ($exists) {
                                $fail('Já existe um registro de estoque para esta loja e este produto/variação.');
                            }
                        },
                    ]),
                Select::make('product_id')
                    ->label('Produto')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query->when(
                            $get('store_id'),
                            fn (Builder $query, mixed $storeId): Builder => $query->where(
                                'company_id',
                                is_numeric($storeId) ? Store::find($storeId)?->company_id : null,
                            ),
                        ),
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->helperText('Preencha o produto OU a variação abaixo, nunca os dois.')
                    ->required(fn (Get $get): bool => blank($get('product_variant_id')))
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            if (filled($value) && filled($get('product_variant_id'))) {
                                $fail('Selecione um produto ou uma variação, nunca os dois.');
                            }
                        },
                    ]),
                Select::make('product_variant_id')
                    ->label('Variação')
                    ->searchable()
                    ->live()
                    ->getSearchResultsUsing(fn (string $search, Get $get): array => ProductVariant::query()
                        ->with('product')
                        ->when(
                            $get('store_id'),
                            fn (Builder $query, mixed $storeId): Builder => $query->whereHas(
                                'product',
                                fn (Builder $query): Builder => $query->where(
                                    'company_id',
                                    is_numeric($storeId) ? Store::find($storeId)?->company_id : null,
                                ),
                            ),
                        )
                        ->where(fn (Builder $query): Builder => $query
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhereHas('product', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%")))
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn (ProductVariant $variant): array => [$variant->id => self::variantLabel($variant)])
                        ->all())
                    ->getOptionLabelUsing(function (mixed $value): ?string {
                        $variant = ProductVariant::query()->with('product')->find($value);

                        return $variant instanceof ProductVariant ? self::variantLabel($variant) : null;
                    })
                    ->required(fn (Get $get): bool => blank($get('product_id')))
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            $storeId = $get('store_id');

                            if (! is_numeric($value) || ! is_numeric($storeId)) {
                                return;
                            }

                            $variant = ProductVariant::query()->with('product')->find($value);
                            $store = Store::find($storeId);

                            if ($variant !== null && $store !== null && $variant->product->company_id !== $store->company_id) {
                                $fail('Essa variação não pertence à empresa desta loja.');
                            }
                        },
                    ]),
                MoneyInput::make('price')
                    ->label('Preço nesta loja'),
                MoneyInput::make('sale_price')
                    ->label('Preço promocional nesta loja'),
                TextInput::make('quantity')
                    ->label('Estoque nesta loja')
                    ->numeric(),
            ]);
    }

    private static function variantLabel(ProductVariant $variant): string
    {
        return "{$variant->product->title} — ".($variant->sku ?? 'sem SKU');
    }
}
