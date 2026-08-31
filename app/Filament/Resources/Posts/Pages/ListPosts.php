<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateWithAi')
                ->label('Gerar post')
                ->icon(Heroicon::OutlinedSparkles)
                ->url(fn (): string => PostResource::getUrl('generate')),
            CreateAction::make(),
        ];
    }
}
