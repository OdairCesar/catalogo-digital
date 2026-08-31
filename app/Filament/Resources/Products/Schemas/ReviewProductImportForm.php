<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Services\Products\Import\ColumnMapping;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

final class ReviewProductImportForm
{
    /** @var array<string, string> */
    private const TARGET_LABELS = [
        'product_field' => 'Dado do produto',
        'variant_field' => 'Dado desta variação',
        'attribute' => 'Variação (cor, tamanho...)',
        'ignore' => 'Não importar',
    ];

    /** @var array<string, string> */
    private const PRODUCT_FIELD_LABELS = [
        'title' => 'Título',
        'description' => 'Descrição',
        'brand' => 'Marca',
        'sku' => 'SKU',
        'gtin' => 'GTIN',
        'mpn' => 'MPN',
        'condition' => 'Condição',
        'gender' => 'Gênero',
        'age_group' => 'Faixa etária',
        'category' => 'Categoria',
        'base_price' => 'Preço',
        'base_sale_price' => 'Preço promocional',
        'base_stock' => 'Estoque',
        'weight_kg' => 'Peso (kg)',
        'height_cm' => 'Altura (cm)',
        'width_cm' => 'Largura (cm)',
        'length_cm' => 'Comprimento (cm)',
    ];

    /** @var array<string, string> */
    private const VARIANT_FIELD_LABELS = [
        'sku' => 'SKU da variação',
        'price' => 'Preço',
        'sale_price' => 'Preço promocional',
        'stock' => 'Estoque',
        'weight_kg' => 'Peso (kg)',
        'height_cm' => 'Altura (cm)',
        'width_cm' => 'Largura (cm)',
        'length_cm' => 'Comprimento (cm)',
    ];

    /**
     * One-line, plain-language summary of what a column becomes — this is
     * what admins read by default; the form below is only for fixing one.
     */
    public static function summaryLine(ColumnMapping $column): string
    {
        return match ($column->target) {
            'product_field' => (self::PRODUCT_FIELD_LABELS[$column->field] ?? $column->field).' do produto',
            'variant_field' => (self::VARIANT_FIELD_LABELS[$column->field] ?? $column->field).' da variação',
            'attribute' => "Variação \"{$column->field}\"",
            default => 'Ignorada',
        };
    }

    /**
     * The 3 fields used to edit a single column, shown inside a modal — not
     * a full-page form, since only one column is being fixed at a time.
     *
     * @return array<int, Component>
     */
    public static function columnFields(): array
    {
        return [
            Select::make('target')
                ->label('O que essa coluna representa?')
                ->options(self::TARGET_LABELS)
                ->live()
                ->required(),
            Select::make('field')
                ->label('Qual campo?')
                ->options(fn (Get $get): array => match ($get('target')) {
                    'product_field' => self::PRODUCT_FIELD_LABELS,
                    'variant_field' => self::VARIANT_FIELD_LABELS,
                    default => [],
                })
                ->visible(fn (Get $get): bool => in_array($get('target'), ['product_field', 'variant_field'], true))
                ->required(fn (Get $get): bool => in_array($get('target'), ['product_field', 'variant_field'], true)),
            TextInput::make('attribute_name')
                ->label('Nome da variação')
                ->placeholder('Ex: Cor, Tamanho')
                ->visible(fn (Get $get): bool => $get('target') === 'attribute')
                ->required(fn (Get $get): bool => $get('target') === 'attribute'),
        ];
    }
}
