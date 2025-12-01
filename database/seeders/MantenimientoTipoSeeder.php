<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MantenimientoTipoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mantenimiento_tipo')->insert([
            [
                'id_tipo' => 1,
                'clave' => 'SERVICIO_A',
                'nombre' => 'Servicio menor',
                'descripcion' => 'Cambio de aceite, filtro y revisión general.',
                'periodicidad_km' => 10000,
                'periodicidad_dias' => 180,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_tipo' => 2,
                'clave' => 'SERVICIO_B',
                'nombre' => 'Servicio intermedio',
                'descripcion' => 'Aceite sintético, filtros, frenos y fluidos.',
                'periodicidad_km' => 15000,
                'periodicidad_dias' => 180,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_tipo' => 3,
                'clave' => 'SERVICIO_C',
                'nombre' => 'Servicio mayor',
                'descripcion' => 'Frenos, filtros avanzados, bujías, afinación.',
                'periodicidad_km' => 20000,
                'periodicidad_dias' => 365,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_tipo' => 4,
                'clave' => 'FRENOS',
                'nombre' => 'Revisión de frenos',
                'descripcion' => 'Inspección y reemplazo de pastillas y discos.',
                'periodicidad_km' => 15000,
                'periodicidad_dias' => 180,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_tipo' => 5,
                'clave' => 'LLANTAS',
                'nombre' => 'Rotación y balanceo de llantas',
                'descripcion' => 'Rotación, balanceo y revisión de desgaste.',
                'periodicidad_km' => 10000,
                'periodicidad_dias' => 120,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_tipo' => 6,
                'clave' => 'ELECTRICO',
                'nombre' => 'Servicio para vehículos eléctricos',
                'descripcion' => 'Revisión de batería, regenerativos y sistemas HV.',
                'periodicidad_km' => 15000,
                'periodicidad_dias' => 365,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_tipo' => 7,
                'clave' => 'SUSPENSION',
                'nombre' => 'Revisión de suspensión',
                'descripcion' => 'Inspección de amortiguadores y brazos.',
                'periodicidad_km' => 20000,
                'periodicidad_dias' => 365,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
