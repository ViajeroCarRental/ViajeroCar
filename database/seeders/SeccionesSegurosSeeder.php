<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeccionesSegurosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('secciones_seguros')->insert([
            ['id_seccion' => 1, 'nombre' => 'Colisión y Robo', 'requiere_desglose_autos' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id_seccion' => 2, 'nombre' => 'Gastos Médicos', 'requiere_desglose_autos' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id_seccion' => 3, 'nombre' => 'Asistencia en el camino', 'requiere_desglose_autos' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id_seccion' => 4, 'nombre' => 'Daños a terceros', 'requiere_desglose_autos' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id_seccion' => 5, 'nombre' => 'Protecciones Automáticas', 'requiere_desglose_autos' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
