<?php

namespace App\Filament\Resources\FaqGroups\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\ExtraFieldsRepeater;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FaqGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título do grupo')
                    ->placeholder('Ex: Tamanhos e caimento')
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('data.faq')
                    ->label('Perguntas')
                    ->schema([
                        TextInput::make('question')
                            ->label('Pergunta')
                            ->required(),
                        Textarea::make('answer')
                            ->label('Resposta')
                            ->required()
                            ->rows(2),
                    ])
                    ->itemLabel(fn (array $state): ?string => is_string($state['question'] ?? null) ? $state['question'] : null)
                    ->collapsible()
                    ->addActionLabel('Adicionar pergunta')
                    ->required()
                    ->minItems(1)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Published)
                    ->required(),
                ExtraFieldsRepeater::make(),
            ]);
    }
}
