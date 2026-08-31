<?php

namespace App\Filament\Resources\PageBlocks\Schemas;

use App\Enums\SectionType;
use App\Filament\Support\Forms\ExtraFieldsRepeater;
use App\Models\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;

class PageBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Topo (hero)')
                    ->visible(fn (?Section $record): bool => $record?->type === SectionType::HomeHero)
                    ->schema([
                        TextInput::make('data.badge')
                            ->label('Selo')
                            ->placeholder('Ex: Coleção Abraço · do 36 ao 52'),
                        TextInput::make('title')
                            ->label('Título')
                            ->required(),
                        Textarea::make('content')
                            ->label('Parágrafo')
                            ->required()
                            ->rows(3),
                        TextInput::make('data.cta_label')
                            ->label('Texto do botão')
                            ->placeholder('Ex: Ver a coleção'),
                    ])
                    ->columnSpanFull(),

                FormSection::make('Barra de confiança')
                    ->visible(fn (?Section $record): bool => $record?->type === SectionType::HomeTrustBar)
                    ->schema([
                        Repeater::make('data.items')
                            ->label('Itens')
                            ->schema([
                                TextInput::make('title')->label('Título')->required(),
                                TextInput::make('subtitle')->label('Subtítulo')->required(),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => is_string($state['title'] ?? null) ? $state['title'] : null)
                            ->collapsible()
                            ->addActionLabel('Adicionar item'),
                    ])
                    ->columnSpanFull(),

                FormSection::make('Banner WhatsApp')
                    ->visible(fn (?Section $record): bool => $record?->type === SectionType::HomeWhatsappBanner)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required(),
                        TextInput::make('data.subtitle')
                            ->label('Subtítulo'),
                        Textarea::make('content')
                            ->label('Texto')
                            ->required()
                            ->rows(3),
                        TextInput::make('data.button_label')
                            ->label('Texto do botão')
                            ->required(),
                    ])
                    ->columnSpanFull(),

                FormSection::make('Sobre')
                    ->visible(fn (?Section $record): bool => $record?->type === SectionType::About)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título principal (H1)')
                            ->required(),
                        Repeater::make('data.intro_paragraphs')
                            ->label('Parágrafos de introdução')
                            ->schema([
                                Textarea::make('paragraph')->label('Parágrafo')->required()->rows(2),
                            ])
                            ->addActionLabel('Adicionar parágrafo'),

                        FormSection::make('Cards')
                            ->schema([
                                Textarea::make('data.missao_text')
                                    ->label('Texto do card "Missão"')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('data.quem_escolhe_text')
                                    ->label('Texto do card "Quem escolhe"')
                                    ->required()
                                    ->rows(2),
                            ]),

                        FormSection::make('Valores')
                            ->schema([
                                Repeater::make('data.values')
                                    ->label('Itens')
                                    ->schema([
                                        TextInput::make('title')->label('Título')->required(),
                                        Textarea::make('desc')->label('Descrição')->required()->rows(2),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => is_string($state['title'] ?? null) ? $state['title'] : null)
                                    ->collapsible()
                                    ->addActionLabel('Adicionar valor'),
                            ]),

                        FormSection::make('Manifesto')
                            ->schema([
                                Repeater::make('data.manifesto_paragraphs')
                                    ->label('Parágrafos')
                                    ->schema([
                                        Textarea::make('paragraph')->label('Parágrafo')->required()->rows(2),
                                    ])
                                    ->addActionLabel('Adicionar parágrafo'),
                                TextInput::make('data.manifesto_tagline')
                                    ->label('Frase de destaque'),
                            ]),

                        FormSection::make('Chamada final (CTA)')
                            ->schema([
                                TextInput::make('data.cta_title')
                                    ->label('Título')
                                    ->required(),
                                Textarea::make('data.cta_description')
                                    ->label('Descrição')
                                    ->rows(2),
                                TextInput::make('data.cta_button_label')
                                    ->label('Texto do botão')
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull(),

                ExtraFieldsRepeater::make(),
            ]);
    }
}
