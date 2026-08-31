<?php

namespace App\Filament\Resources\ToolSubmissions\Schemas;

use App\Enums\ToolSubmissionStatus;
use App\Models\ToolSubmission;
use App\Services\Tools\ToolRegistry;
use App\Services\Tools\ToolResultPresenter;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ToolSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tool_slug')
                    ->label('Ferramenta')
                    ->formatStateUsing(function (?string $state): string {
                        $tool = app(ToolRegistry::class)->find((string) $state);

                        return $tool !== null ? $tool->title : (string) $state;
                    })
                    ->disabled(),
                TextInput::make('name')
                    ->label('Nome')
                    ->disabled(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->disabled(),
                TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->disabled(),
                TextInput::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (ToolSubmissionStatus|string|null $state): string => $state instanceof ToolSubmissionStatus
                        ? $state->getLabel()
                        : ToolSubmissionStatus::tryFrom((string) $state)?->getLabel() ?? '')
                    ->disabled(),
                TextInput::make('questions_asked')
                    ->label('Perguntas feitas pela IA')
                    ->disabled(),

                Section::make('Conversa')
                    ->schema([
                        TextEntry::make('messages')
                            ->hiddenLabel()
                            ->html()
                            ->state(fn (ToolSubmission $record): string => view('filament.tools.messages-entry', [
                                'messages' => $record->messages ?? [],
                            ])->render()),
                    ])
                    ->visible(fn (ToolSubmission $record): bool => filled($record->messages))
                    ->columnSpanFull(),

                Section::make('Resultado gerado')
                    ->schema([
                        TextEntry::make('result')
                            ->hiddenLabel()
                            ->html()
                            ->state(function (ToolSubmission $record): string {
                                if (! $record->result) {
                                    return '<p style="color: #94a3b8; margin: 0;">Nenhum resultado gerado ainda.</p>';
                                }

                                $tool = app(ToolRegistry::class)->find($record->tool_slug);

                                if ($tool === null) {
                                    return e(json_encode($record->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '');
                                }

                                $rows = app(ToolResultPresenter::class)->rows($tool, $record->result);

                                return view('filament.tools.result-entry', ['rows' => $rows])->render();
                            }),
                    ])
                    ->columnSpanFull(),

                TextInput::make('ai_model')
                    ->label('Modelo de IA')
                    ->disabled(),
                Textarea::make('ai_error')
                    ->label('Erro da IA')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('source_url')
                    ->label('Enviado a partir de')
                    ->url()
                    ->disabled(),
                DateTimePicker::make('read_at')
                    ->label('Lido em'),
            ]);
    }
}
