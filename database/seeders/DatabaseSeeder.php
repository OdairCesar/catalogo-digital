<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Senha fixa ("password") por conveniência local — este seeder nunca deve rodar em produção.
        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ServiceSeeder/StateSeeder/CitySeeder alimentam o módulo "servicos" (agência
        // de tecnologia), desativado nesta implantação — rode-os manualmente
        // (`--class=ServiceSeeder`) só se reativar esse módulo.
        $this->call([
            FitByCaeCatalogSeeder::class,
            SectionSeeder::class,
        ]);
    }
}
