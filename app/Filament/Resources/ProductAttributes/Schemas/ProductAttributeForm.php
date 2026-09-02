<?php

namespace App\Filament\Resources\ProductAttributes\Schemas;

use App\Filament\Support\Forms\CloudinaryImageUpload;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductAttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome do atributo')
                    ->helperText('Ex: Cor, Tamanho, Volume.')
                    ->required()
                    ->unique(ignoreRecord: true),
                Repeater::make('values')
                    ->label('Valores')
                    ->relationship()
                    ->rules([
                        function (): Closure {
                            return function (string $attribute, mixed $value, Closure $fail): void {
                                $values = collect((array) $value)
                                    ->map(function (mixed $row): string {
                                        $rowValue = is_array($row) ? ($row['value'] ?? null) : null;

                                        return is_string($rowValue) ? mb_strtolower(trim($rowValue)) : '';
                                    })
                                    ->filter(fn (string $value): bool => $value !== '');

                                if ($values->count() !== $values->unique()->count()) {
                                    $fail('Existem valores duplicados.');
                                }
                            };
                        },
                    ])
                    ->schema([
                        TextInput::make('value')
                            ->label('Valor')
                            ->helperText('Ex: Azul, Vermelho, P, M, G.')
                            ->required(),
                        ColorPicker::make('hex')
                            ->label('Cor'),
                        CloudinaryImageUpload::make('image')
                            ->label('Imagem'),
                    ])
                    ->columns(3)
                    ->addActionLabel('Adicionar valor')
                    ->defaultItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => is_string($state['value'] ?? null) ? $state['value'] : null)
                    ->columnSpanFull(),
            ]);
    }
}
