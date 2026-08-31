<?php

namespace App\Filament\Resources\ServiceClusterLandingPages\Schemas;

use App\Filament\Support\Forms\LandingPageFormFields;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ServiceClusterLandingPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_cluster_id')
                    ->label('Cluster')
                    ->relationship('serviceCluster', 'title')
                    ->searchable()
                    ->required(),
                Select::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->required(),
                ...LandingPageFormFields::common(
                    'Gerado automaticamente como {cluster}-em-{cidade}; só altere se souber o que está fazendo.',
                ),
            ]);
    }
}
