<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Support\Actions\ViewOnLandingAction;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * @extends EditRecord<Post>
 */
class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewOnLandingAction::make(
                url: fn (): string => route('blog.show', $this->record),
                visible: fn (): bool => $this->getRecord()->status === PostStatus::Published,
                label: 'Ver post',
            ),
            DeleteAction::make(),
        ];
    }
}
