<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstatusCarroSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estatus_carro')->insert([
            ['nombre' => 'Disponible',     'descripcion' => 'Vehículo disponible para renta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Mantenimiento',  'descripcion' => 'Vehículo temporalmente fuera de servicio', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Rentado',        'descripcion' => 'Vehículo actualmente rentado', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Baja',           'descripcion' => 'Vehículo fuera del sistema o dado de baja', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
