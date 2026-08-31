<?php

namespace App\Filament\Resources\InstagramPosts\Pages;

use App\Enums\SectionType;
use App\Filament\Resources\InstagramPosts\InstagramPostResource;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListInstagramPosts extends ListRecords
{
    protected static string $resource = InstagramPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editIntroText')
                ->label('Editar textos')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->modalHeading('Textos do bloco Instagram')
                ->fillForm(fn (): array => Section::block(SectionType::Instagram)?->only(['title', 'content']) ?? [])
                ->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required(),
                    Textarea::make('content')
                        ->label('Descrição')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    Section::query()->updateOrCreate(['type' => SectionType::Instagram], $data);

                    Notification::make()->title('Textos atualizados')->success()->send();
                }),
            CreateAction::make(),
        ];
    }
}
