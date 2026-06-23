<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SistemasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sistemas')->insert([
            ['id_sistema' => 1, 'codigo' => 'VIAJERO',
             'nombre' => 'Viajero Car Rental', 'pasarela' => 'stripe',
             'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id_sistema' => 2, 'codigo' => 'ONEDREENG',
             'nombre' => 'OneDreeng', 'pasarela' => 'stripe',
             'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}