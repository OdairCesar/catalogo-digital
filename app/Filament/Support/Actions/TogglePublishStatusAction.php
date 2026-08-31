<?php

namespace App\Filament\Support\Actions;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * Quick row action to toggle a model's status between `Published` and
 * `Draft`, for any backed status enum that defines both cases (e.g.
 * PageStatus, PostStatus, ServiceClusterStatus). Hidden for any other case
 * (e.g. Generating, Failed), since those are not manually toggleable.
 */
final class TogglePublishStatusAction
{
    public static function make(string $statusEnum): Action
    {
        return Action::make('togglePublishStatus')
            ->label(fn (Model $record): string => self::isPublished($record, $statusEnum) ? 'Despublicar' : 'Publicar')
            ->icon(fn (Model $record): BackedEnum => self::isPublished($record, $statusEnum) ? Heroicon::OutlinedArrowUturnLeft : Heroicon::OutlinedCheckCircle)
            ->color(fn (Model $record): string => self::isPublished($record, $statusEnum) ? 'gray' : 'success')
            ->visible(fn (Model $record): bool => in_array($record->getAttribute('status'), [$statusEnum::Published, $statusEnum::Draft], true))
            ->requiresConfirmation()
            ->action(fn (Model $record) => $record->update([
                'status' => self::isPublished($record, $statusEnum) ? $statusEnum::Draft : $statusEnum::Published,
            ]));
    }

    private static function isPublished(Model $record, string $statusEnum): bool
    {
        return $record->getAttribute('status') === $statusEnum::Published;
    }
}
