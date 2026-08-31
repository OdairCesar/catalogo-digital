<?php

namespace App\Filament\Support\Actions;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared "read_at" tracking actions for the inbox-style resources
 * (Lead and ToolSubmission), which only differ in the model they operate on.
 */
final class ReadTrackingActions
{
    public static function markAsReadAction(): Action
    {
        return Action::make('markAsRead')
            ->label('Marcar lido')
            ->icon(Heroicon::OutlinedCheck)
            ->visible(fn (Model $record): bool => $record->getAttribute('read_at') === null)
            ->action(fn (Model $record) => $record->update(['read_at' => now()]));
    }

    /**
     * @return array<int, BulkAction>
     */
    public static function bulkActions(): array
    {
        return [
            BulkAction::make('markAsRead')
                ->label('Marcar lidos')
                ->icon(Heroicon::OutlinedCheck)
                ->action(fn (Collection $records) => $records->each->update(['read_at' => now()]))
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('markAsUnread')
                ->label('Marcar pendentes')
                ->icon(Heroicon::OutlinedXMark)
                ->action(fn (Collection $records) => $records->each->update(['read_at' => null]))
                ->deselectRecordsAfterCompletion(),
        ];
    }
}
