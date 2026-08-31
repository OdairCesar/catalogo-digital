<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\PageStatus;
use App\Enums\Weekday;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Empresa')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Dados básicos')
                            ->icon(Heroicon::OutlinedIdentification)
                            ->columns(2)
                            ->components([
                                AutoSlug::attach(
                                    TextInput::make('name')
                                        ->label('Razão social')
                                        ->required(),
                                ),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Usado na URL, ex: /empresas/{slug}.'),
                                TextInput::make('cnpj'),
                                Select::make('status')
                                    ->options(PageStatus::class)
                                    ->default(PageStatus::Draft)
                                    ->required(),
                            ]),
                        Tab::make('Identidade visual')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->columns(2)
                            ->components([
                                CloudinaryImageUpload::make('logo')
                                    ->helperText('Exibido no cabeçalho e rodapé do site.'),
                                CloudinaryImageUpload::make('favicon')
                                    ->label('Favicon')
                                    ->helperText('Ícone exibido na aba do navegador. Recomendado: imagem quadrada (ex: 512x512px).'),
                            ]),
                        Tab::make('Institucional')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->columns(2)
                            ->components([
                                TextInput::make('site_name')
                                    ->label('Nome do site')
                                    ->helperText('Exibido no rodapé, cabeçalho e SEO. Deixe em branco para usar a razão social.'),
                                TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->tel()
                                    ->helperText('Ex: (14) 99274-6599.'),
                                TextInput::make('email')
                                    ->label('E-mail')
                                    ->email(),
                                TextInput::make('instagram_url')
                                    ->label('Instagram')
                                    ->url(),
                                TextInput::make('facebook_url')
                                    ->label('Facebook')
                                    ->url(),
                                Textarea::make('short_description')
                                    ->label('Descrição curta')
                                    ->helperText('Usada no rodapé e como descrição institucional para SEO.')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Endereço')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->columns(3)
                            ->components([
                                TextInput::make('address_zip_code')
                                    ->label('CEP'),
                                TextInput::make('address_street')
                                    ->label('Logradouro')
                                    ->columnSpan(2),
                                TextInput::make('address_number')
                                    ->label('Número'),
                                TextInput::make('address_complement')
                                    ->label('Complemento'),
                                TextInput::make('address_neighborhood')
                                    ->label('Bairro'),
                                TextInput::make('address_city')
                                    ->label('Cidade'),
                                TextInput::make('address_state')
                                    ->label('UF')
                                    ->maxLength(2),
                            ]),
                        Tab::make('Horário de funcionamento')
                            ->icon(Heroicon::OutlinedClock)
                            ->components(
                                array_map(
                                    fn (Weekday $day): Fieldset => Fieldset::make($day->getLabel())
                                        ->columns(3)
                                        ->components([
                                            Checkbox::make("opening_hours.{$day->value}.closed")
                                                ->label('Fechado')
                                                ->live()
                                                ->dehydrated(),
                                            TimePicker::make("opening_hours.{$day->value}.open")
                                                ->label('Abertura')
                                                ->seconds(false)
                                                ->hidden(fn (Get $get): bool => (bool) $get("opening_hours.{$day->value}.closed")),
                                            TimePicker::make("opening_hours.{$day->value}.close")
                                                ->label('Fechamento')
                                                ->seconds(false)
                                                ->hidden(fn (Get $get): bool => (bool) $get("opening_hours.{$day->value}.closed")),
                                        ]),
                                    Weekday::cases(),
                                ),
                            ),
                    ]),
            ]);
    }
}
