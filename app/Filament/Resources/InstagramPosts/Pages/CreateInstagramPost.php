<?php

namespace App\Filament\Resources\InstagramPosts\Pages;

use App\Filament\Concerns\SetsSectionTypeOnCreate;
use App\Filament\Resources\InstagramPosts\InstagramPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInstagramPost extends CreateRecord
{
    use SetsSectionTypeOnCreate;

    protected static string $resource = InstagramPostResource::class;
}
