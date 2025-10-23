<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiciosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'nombre'      => 'Silla de seguridad para niños',
                'descripcion' => 'Para niños de 18 a 36 kg aprox.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 150.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Upgrade de categoría',
                'descripcion' => 'Cambia a una categoría superior.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 200.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Dispositivo GPS',
                'descripcion' => 'Cobertura funcional en Querétaro.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 200.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Conductor adicional',
                'descripcion' => 'Agrega un conductor extra.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 150.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        // Inserta o ignora si ya existen (por el unique de nombre)
        foreach ($rows as $row) {
            DB::table('servicios')->updateOrInsert(
                ['nombre' => $row['nombre']],
                $row
            );
        }
    }
}
