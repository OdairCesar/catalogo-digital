<?php

namespace App\Filament\Resources\ToolSubmissions\Tables;

use App\Enums\ToolSubmissionStatus;
use App\Filament\Support\Actions\ReadTrackingActions;
use App\Filament\Support\Tables\DateRangeFilter;
use App\Models\ToolSubmission;
use App\Services\Tools\ToolDefinition;
use App\Services\Tools\ToolRegistry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ToolSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tool_slug')
                    ->label('Ferramenta')
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        $tool = app(ToolRegistry::class)->find($state);

                        return $tool !== null ? $tool->title : $state;
                    }),
                TextColumn::make('name')
                    ->label('Nome')
                    ->weight(fn (ToolSubmission $record): string => $record->read_at === null ? 'bold' : 'normal')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefone'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('questions_asked')
                    ->label('Perguntas')
                    ->alignCenter(),
                TextColumn::make('read_at')
                    ->label('Lido em')
                    ->dateTime()
                    ->placeholder('Não lido')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Recebido em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tool_slug')
                    ->label('Ferramenta')
                    ->options(fn (): array => collect(app(ToolRegistry::class)->all())
                        ->mapWithKeys(fn (ToolDefinition $tool): array => [$tool->slug => $tool->title])
                        ->all()),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn (): array => collect(ToolSubmissionStatus::cases())
                        ->mapWithKeys(fn (ToolSubmissionStatus $status): array => [$status->value => $status->getLabel()])
                        ->all()),
                TernaryFilter::make('read_at')
                    ->label('Leitura')
                    ->nullable()
                    ->trueLabel('Lidos')
                    ->falseLabel('Não lidos')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('read_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('read_at'),
                    ),
                DateRangeFilter::make('created_at', 'Recebido em'),
            ])
            ->recordActions([
                ReadTrackingActions::markAsReadAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...ReadTrackingActions::bulkActions(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
