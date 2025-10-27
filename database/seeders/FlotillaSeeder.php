<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlotillaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('flotillas')->insert([
            [
                'nombre' => 'Flotilla Central',
                'descripcion' => 'Vehículos administrativos y de gerencia',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Flotilla Norte',
                'descripcion' => 'SUVs y pickups para sucursal norte',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Flotilla Sur',
                'descripcion' => 'Vehículos compactos para renta local',
                'activa' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Flotilla Demo',
                'descripcion' => '🚗 Auto de prueba — Conexión directa sin modelos',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
