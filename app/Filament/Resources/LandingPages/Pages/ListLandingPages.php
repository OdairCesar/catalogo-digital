<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Resources\LandingPages\LandingPageResource;
use App\Jobs\RegenerateLandingPages;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class ListLandingPages extends ListRecords
{
    protected static string $resource = LandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('regenerateAll')
                ->label('Regenerar tudo')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Regenerar todas as landing pages')
                ->modalDescription('Isso vai apagar todas as landing pages existentes, incluindo H1, meta title/description, canonical, robots e CTA customizados manualmente, e recriar uma nova para cada combinação de serviço e cidade com os valores padrão. A ação roda em segundo plano.')
                ->modalSubmitActionLabel('Sim, regenerar')
                ->action(function (): void {
                    if (! Cache::add(RegenerateLandingPages::LOCK_KEY, true, now()->addMinutes(30))) {
                        Notification::make()
                            ->title('Já em andamento')
                            ->body('Uma regeneração já está rodando. Aguarde ela terminar antes de disparar outra.')
                            ->warning()
                            ->send();

                        return;
                    }

                    RegenerateLandingPages::dispatch();

                    Notification::make()
                        ->title('Regeneração iniciada')
                        ->body('As landing pages estão sendo recriadas em segundo plano. A lista vai refletir o resultado assim que o processo terminar.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
