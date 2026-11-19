<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(IdentitySeeder::class);

        // Usuario de prueba del scaffolding inicial: retirado del seed
        // canónico (F2). Las cuentas reales se crean desde la UI de
        // identidad con rol y contraseña; los tests usan factories.
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
