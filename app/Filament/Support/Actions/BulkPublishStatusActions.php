<?php

namespace App\Filament\Support\Actions;

use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Bulk "publish" / "back to draft" actions for any backed status enum that
 * defines `Published` and `Draft` cases (e.g. PageStatus, PostStatus,
 * ServiceClusterStatus).
 */
final class BulkPublishStatusActions
{
    /**
     * @return array<int, BulkAction>
     */
    public static function make(string $statusEnum): array
    {
        return [
            BulkAction::make('publish')
                ->label('Publicar')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->action(fn (Collection $records) => $records->each->update(['status' => $statusEnum::Published]))
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('unpublish')
                ->label('Despublicar')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('gray')
                ->requiresConfirmation()
                ->action(fn (Collection $records) => $records->each->update(['status' => $statusEnum::Draft]))
                ->deselectRecordsAfterCompletion(),
        ];
    }
}
