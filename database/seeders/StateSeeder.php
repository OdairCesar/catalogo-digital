<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        State::query()->firstOrCreate(
            ['uf' => 'SP'],
            [
                'slug' => 'sao-paulo',
                'name' => 'São Paulo',
                'intro' => 'São Paulo é o estado mais populoso e o principal polo econômico do Brasil, com forte presença industrial, tecnológica e de serviços.',
                'business_text' => 'Empresas paulistas competem em um dos mercados mais dinâmicos e digitalizados do país, o que exige presença online sólida e sistemas robustos para acompanhar o ritmo de crescimento.',
                'status' => PageStatus::Published,
            ],
        );
    }
}
