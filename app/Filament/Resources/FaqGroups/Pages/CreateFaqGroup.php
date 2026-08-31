<?php

namespace App\Filament\Resources\FaqGroups\Pages;

use App\Filament\Concerns\SetsSectionTypeOnCreate;
use App\Filament\Resources\FaqGroups\FaqGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaqGroup extends CreateRecord
{
    use SetsSectionTypeOnCreate;

    protected static string $resource = FaqGroupResource::class;
}
