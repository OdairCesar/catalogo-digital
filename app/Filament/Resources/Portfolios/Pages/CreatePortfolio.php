<?php

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Concerns\SetsSectionTypeOnCreate;
use App\Filament\Resources\Portfolios\PortfolioResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePortfolio extends CreateRecord
{
    use SetsSectionTypeOnCreate;

    protected static string $resource = PortfolioResource::class;
}
