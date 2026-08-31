<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\PageStatus;
use App\Enums\ProductAgeGroup;
use App\Enums\ProductCondition;
use App\Enums\ProductGender;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use App\Filament\Support\Forms\MoneyInput;
use App\Models\ProductAttributeValue;
use App\Services\Products\VariantGridGenerator;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Produto')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informações gerais')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columns(2)
                            ->components([
                                Select::make('company_id')
                                    ->label('Empresa')
                                    ->relationship('company', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('product_category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        AutoSlug::attach(
                                            TextInput::make('name')
                                                ->label('Nome')
                                                ->required(),
                                        ),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(table: 'product_categories', column: 'slug'),
                                        TextInput::make('google_product_category')
                                            ->label('Categoria no Google')
                                            ->helperText('Código ou nome da taxonomia do Google Merchant Center.'),
                                    ]),
                                AutoSlug::attach(
                                    TextInput::make('title')
                                        ->label('Título')
                                        ->required(),
                                ),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Usado na URL, ex: /produtos/{slug}.'),
                                TextInput::make('sku')
                                    ->label('SKU'),
                                Select::make('status')
                                    ->options(PageStatus::class)
                                    ->default(PageStatus::Draft)
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Descrição')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Identificação (Google Shopping)')
                            ->icon(Heroicon::OutlinedShoppingBag)
                            ->components([
                                Section::make()
                                    ->description('Usado no feed do Google Shopping.')
                                    ->columns(3)
                                    ->components([
                                        TextInput::make('brand')
                                            ->label('Marca')
                                            ->live(onBlur: true),
                                        TextInput::make('gtin')
                                            ->label('GTIN')
                                            ->rule('regex:/^(\d{8}|\d{12,14})$/')
                                            ->helperText('8, 12, 13 ou 14 dígitos numéricos (UPC, EAN, JAN, ISBN ou ITF-14).'),
                                        TextInput::make('mpn')
                                            ->label('MPN'),
                                        Select::make('condition')
                                            ->label('Condição')
                                            ->options(ProductCondition::class)
                                            ->default(ProductCondition::New)
                                            ->required(),
                                        Select::make('gender')
                                            ->label('Gênero')
                                            ->options(ProductGender::class)
                                            ->helperText('Para roupas.'),
                                        Select::make('age_group')
                                            ->label('Faixa etária')
                                            ->options(ProductAgeGroup::class)
                                            ->helperText('Para roupas.'),
                                    ]),
                            ]),
                        Tab::make('Preço, estoque e cubagem')
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->components([
                                MoneyInput::make('base_price')
                                    ->label('Preço'),
                                MoneyInput::make('base_sale_price')
                                    ->label('Preço promocional'),
                                TextInput::make('base_stock')
                                    ->label('Estoque')
                                    ->numeric(),
                                Section::make('Cubagem')
                                    ->description('Peso e dimensões usados nos dados de frete do feed.')
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->components([
                                        TextInput::make('weight_kg')
                                            ->label('Peso (kg)')
                                            ->numeric(),
                                        TextInput::make('height_cm')
                                            ->label('Altura (cm)')
                                            ->numeric(),
                                        TextInput::make('width_cm')
                                            ->label('Largura (cm)')
                                            ->numeric(),
                                        TextInput::make('length_cm')
                                            ->label('Comprimento (cm)')
                                            ->numeric(),
                                    ]),
                            ])
                            ->columns(3),
                        Tab::make('Imagens')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->components([
                                CloudinaryImageUpload::make('cover_image')
                                    ->label('Imagem principal')
                                    ->live(),
                                CloudinaryImageUpload::make('images')
                                    ->label('Galeria')
                                    ->multiple()
                                    ->columnSpanFull(),
                                Text::make(function (Get $get): HtmlString {
                                    $reasons = [];

                                    if (blank($get('brand'))) {
                                        $reasons[] = 'sem marca';
                                    }

                                    if (blank($get('cover_image'))) {
                                        $reasons[] = 'sem imagem principal';
                                    }

                                    if ($reasons === []) {
                                        return new HtmlString('');
                                    }

                                    return new HtmlString(
                                        '<div class="rounded-lg border border-warning-300 bg-warning-50 px-3 py-2 text-sm text-warning-700">'
                                        .'⚠ Este produto não vai aparecer no feed do Google Shopping: '.implode(', ', $reasons).'.'
                                        .'</div>',
                                    );
                                })
                                    ->visible(fn (Get $get): bool => blank($get('brand')) || blank($get('cover_image')))
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Variações')
                            ->icon(Heroicon::OutlinedSquares2x2)
                            ->components([
                                Section::make()
                                    ->description('Deixe vazio se este produto não tem variações.')
                                    ->afterHeader([
                                        Action::make('generateVariants')
                                            ->label('Gerar grade automaticamente')
                                            ->icon(Heroicon::OutlinedSquares2x2)
                                            ->modalWidth('lg')
                                            ->form([
                                                Repeater::make('attribute_groups')
                                                    ->label('Atributos e valores')
                                                    ->helperText('Digite o nome do atributo (ex: Cor) e todos os valores possíveis (aperte Enter depois de cada um). Se o atributo ou o valor já existir, ele é reaproveitado; se não existir, é criado na hora.')
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->label('Atributo')
                                                            ->placeholder('Ex: Cor')
                                                            ->required(),
                                                        TagsInput::make('values')
                                                            ->label('Valores')
                                                            ->placeholder('Digite um valor e aperte Enter')
                                                            ->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1)
                                                    ->addActionLabel('Adicionar atributo')
                                                    ->required(),
                                            ])
                                            ->action(function (array $data, Get $get, Set $set, VariantGridGenerator $generator): void {
                                                $attributeGroups = is_array($data['attribute_groups'] ?? null) ? $data['attribute_groups'] : [];
                                                $existingVariants = is_array($get('variants')) ? $get('variants') : [];

                                                $set('variants', $generator->generateFromAttributeGroups(
                                                    array_values($attributeGroups),
                                                    $existingVariants,
                                                ));

                                                Notification::make()
                                                    ->title('Grade gerada com sucesso')
                                                    ->body('Confira os valores de preço e estoque gerados antes de salvar.')
                                                    ->success()
                                                    ->send();
                                            }),
                                    ])
                                    ->components([
                                        Repeater::make('variants')
                                            ->hiddenLabel()
                                            ->relationship()
                                            ->defaultItems(0)
                                            ->rules([
                                                function (): Closure {
                                                    return function (string $attribute, mixed $value, Closure $fail): void {
                                                        $seenCombinations = [];

                                                        foreach ((array) $value as $variant) {
                                                            if (! is_array($variant)) {
                                                                continue;
                                                            }

                                                            $ids = collect(is_array($variant['attributeValues'] ?? null) ? $variant['attributeValues'] : [])
                                                                ->filter(fn (mixed $id): bool => is_numeric($id))
                                                                ->map(fn (mixed $id): int => (int) $id)
                                                                ->sort()
                                                                ->values()
                                                                ->all();

                                                            if ($ids === []) {
                                                                continue;
                                                            }

                                                            $key = implode('-', $ids);

                                                            if (in_array($key, $seenCombinations, true)) {
                                                                $fail('Existem variações duplicadas com os mesmos atributos.');

                                                                return;
                                                            }

                                                            $seenCombinations[] = $key;
                                                        }
                                                    };
                                                },
                                            ])
                                            ->schema([
                                                Select::make('attributeValues')
                                                    ->label('Atributos (cor, tamanho...)')
                                                    ->relationship(
                                                        name: 'attributeValues',
                                                        titleAttribute: 'value',
                                                        modifyQueryUsing: fn (Builder $query): Builder => $query->with('attribute'),
                                                    )
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->getOptionLabelFromRecordUsing(fn (ProductAttributeValue $record): string => $record->label())
                                                    ->createOptionForm([
                                                        Select::make('product_attribute_id')
                                                            ->label('Atributo')
                                                            ->relationship('attribute', 'name')
                                                            ->searchable()
                                                            ->preload()
                                                            ->required()
                                                            ->createOptionForm([
                                                                TextInput::make('name')
                                                                    ->label('Nome do atributo')
                                                                    ->helperText('Ex: Cor, Tamanho, Volume.')
                                                                    ->required()
                                                                    ->unique(table: 'product_attributes', column: 'name'),
                                                            ]),
                                                        TextInput::make('value')
                                                            ->label('Valor')
                                                            ->required(),
                                                    ])
                                                    ->columnSpanFull(),
                                                TextInput::make('sku')
                                                    ->label('SKU'),
                                                TextInput::make('stock')
                                                    ->label('Estoque')
                                                    ->numeric(),
                                                Toggle::make('is_active')
                                                    ->label('Ativa')
                                                    ->default(true)
                                                    ->inline(false),
                                                Section::make('Usar dados diferentes do produto principal?')
                                                    ->description('Deixe em branco para usar o preço, a imagem e a cubagem do produto principal.')
                                                    ->collapsible()
                                                    ->collapsed()
                                                    ->columns(4)
                                                    ->columnSpanFull()
                                                    ->components([
                                                        MoneyInput::make('price')
                                                            ->label('Preço')
                                                            ->columnSpan(2),
                                                        MoneyInput::make('sale_price')
                                                            ->label('Preço promocional')
                                                            ->columnSpan(2),
                                                        CloudinaryImageUpload::make('image')
                                                            ->label('Imagem')
                                                            ->columnSpan(4),
                                                        TextInput::make('weight_kg')
                                                            ->label('Peso (kg)')
                                                            ->numeric(),
                                                        TextInput::make('height_cm')
                                                            ->label('Altura (cm)')
                                                            ->numeric(),
                                                        TextInput::make('width_cm')
                                                            ->label('Largura (cm)')
                                                            ->numeric(),
                                                        TextInput::make('length_cm')
                                                            ->label('Comprimento (cm)')
                                                            ->numeric(),
                                                    ]),
                                            ])
                                            ->columns(3)
                                            ->itemLabel(function (Schema $item): ?string {
                                                $selected = $item->getComponent('attributeValues')?->getState();

                                                $ids = collect(is_array($selected) ? $selected : [])
                                                    ->filter(fn (mixed $id): bool => is_numeric($id))
                                                    ->map(fn (mixed $id): int => (int) $id);

                                                if ($ids->isEmpty()) {
                                                    return null;
                                                }

                                                return ProductAttributeValue::query()
                                                    ->with('attribute')
                                                    ->whereIn('id', $ids)
                                                    ->get()
                                                    ->map(fn (ProductAttributeValue $value): string => $value->label())
                                                    ->implode(', ');
                                            })
                                            ->collapsible()
                                            ->collapsed()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Disponibilidade por horário')
                            ->icon(Heroicon::OutlinedClock)
                            ->components([
                                Section::make()
                                    ->description('Sem nenhuma regra, o produto fica sempre disponível.')
                                    ->components([
                                        Repeater::make('availabilities')
                                            ->hiddenLabel()
                                            ->relationship()
                                            ->defaultItems(0)
                                            ->schema([
                                                Select::make('weekday')
                                                    ->label('Dia da semana')
                                                    ->options([
                                                        0 => 'Domingo',
                                                        1 => 'Segunda-feira',
                                                        2 => 'Terça-feira',
                                                        3 => 'Quarta-feira',
                                                        4 => 'Quinta-feira',
                                                        5 => 'Sexta-feira',
                                                        6 => 'Sábado',
                                                    ])
                                                    ->required(),
                                                TimePicker::make('starts_at')
                                                    ->label('Início')
                                                    ->required(),
                                                TimePicker::make('ends_at')
                                                    ->label('Fim')
                                                    ->required(),
                                            ])
                                            ->columns(3)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
