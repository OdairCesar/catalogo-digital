<?php

namespace App\Filament\Support\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Quick row action to generate an AI image for a record directly from the
 * table, mirroring the "Gerar imagem com IA" action already available on the
 * Service, ServiceCluster, and PortfolioItem forms. Only shown while the
 * image column is empty and the record's title (the prompt's main input) has
 * already been set — it may still be blank while an AI-generated record
 * (e.g. a ServiceCluster) is mid-generation.
 */
final class GenerateImageAction
{
    /**
     * @param  Closure  $generate  Returns the generated image's storage path for the given record.
     */
    public static function make(string $imageColumn, Closure $generate): Action
    {
        return Action::make('generateImage')
            ->label('Gerar imagem')
            ->icon(Heroicon::OutlinedSparkles)
            ->visible(fn (Model $record): bool => blank($record->getAttribute($imageColumn)) && filled($record->getAttribute('title')))
            ->requiresConfirmation()
            ->action(function (Model $record) use ($imageColumn, $generate): void {
                // gpt-image-1 generations routinely take 10-30s; guard against
                // hitting a lower default max_execution_time mid-request.
                set_time_limit(180);

                try {
                    $path = $generate($record);
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->title('Não foi possível gerar a imagem')
                        ->body('Tente novamente em instantes.')
                        ->danger()
                        ->send();

                    return;
                }

                $record->update([$imageColumn => $path]);

                Notification::make()
                    ->title('Imagem gerada com sucesso')
                    ->success()
                    ->send();
            });
    }
}
