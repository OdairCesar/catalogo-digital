<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stateId = State::query()->where('uf', 'SP')->value('id');

        $cities = [
            [
                'slug' => 'bauru',
                'name' => 'Bauru',
                'region' => 'Centro-Oeste Paulista',
                'population' => 379_297,
                'gdp' => 12_400_000_000,
                'latitude' => -22.3246,
                'longitude' => -49.0871,
                'intro' => 'Bauru é o principal polo comercial e de serviços do Centro-Oeste Paulista, com forte presença de indústrias, comércio e prestadoras de serviço em expansão.',
                'business_text' => 'Empresas de Bauru competem em um mercado regional cada vez mais digital e precisam de presença online sólida para não perder espaço para concorrentes de fora da região.',
            ],
            [
                'slug' => 'marilia',
                'name' => 'Marília',
                'region' => 'Centro-Oeste Paulista',
                'population' => 240_590,
                'gdp' => 7_100_000_000,
                'latitude' => -22.2139,
                'longitude' => -49.9458,
                'intro' => 'Marília é conhecida como a capital nacional do alimento e concentra um forte setor industrial e agroindustrial na região Centro-Oeste do estado de São Paulo.',
                'business_text' => 'Empresas de Marília, especialmente do setor industrial e agroalimentar, buscam sistemas e sites que acompanhem o ritmo de crescimento da cidade.',
            ],
            [
                'slug' => 'campinas',
                'name' => 'Campinas',
                'region' => 'Região Metropolitana de Campinas',
                'population' => 1_213_792,
                'gdp' => 55_800_000_000,
                'latitude' => -22.9099,
                'longitude' => -47.0626,
                'intro' => 'Campinas é um dos maiores polos tecnológicos e universitários do Brasil, com um ecossistema de startups, indústrias e prestadoras de serviço em constante crescimento.',
                'business_text' => 'Empresas de Campinas competem em um mercado altamente digitalizado e precisam de produtos tecnológicos robustos para se diferenciar da concorrência.',
            ],
            [
                'slug' => 'ribeirao-preto',
                'name' => 'Ribeirão Preto',
                'region' => 'Interior de São Paulo',
                'population' => 720_116,
                'gdp' => 24_300_000_000,
                'latitude' => -21.1775,
                'longitude' => -47.8208,
                'intro' => 'Ribeirão Preto é um dos principais centros econômicos do interior paulista, com forte presença do agronegócio, saúde e serviços.',
                'business_text' => 'Empresas de Ribeirão Preto precisam de sistemas e sites que suportem o alto volume de negócios gerado pelo agronegócio e pelo setor de saúde da região.',
            ],
            [
                'slug' => 'sao-carlos',
                'name' => 'São Carlos',
                'region' => 'Interior de São Paulo',
                'population' => 254_484,
                'gdp' => 9_200_000_000,
                'latitude' => -22.0175,
                'longitude' => -47.8909,
                'intro' => 'São Carlos é reconhecida como a capital da tecnologia do interior paulista, abrigando duas grandes universidades e um ecossistema forte de inovação.',
                'business_text' => 'Empresas de São Carlos, muitas ligadas à tecnologia e inovação, buscam parceiros técnicos capazes de acompanhar o mesmo nível de exigência do mercado local.',
            ],
        ];

        foreach ($cities as $city) {
            $record = City::query()->firstOrCreate(
                ['slug' => $city['slug']],
                [
                    'name' => $city['name'],
                    'state_id' => $stateId,
                    'region' => $city['region'],
                    'population' => $city['population'],
                    'gdp' => $city['gdp'],
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                    'intro' => $city['intro'],
                    'business_text' => $city['business_text'],
                    'status' => PageStatus::Published,
                ],
            );

            // firstOrCreate() skips the create array for rows that already existed
            // before the state_id column was introduced, so backfill it here too.
            if ($record->state_id === null && $stateId !== null) {
                $record->update(['state_id' => $stateId]);
            }
        }
    }
}
