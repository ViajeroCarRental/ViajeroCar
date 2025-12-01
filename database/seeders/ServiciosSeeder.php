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

            // =======================
            // GASOLINA Y LITROS
            // =======================
            [
                'nombre'      => 'Gasolina Prepago',
                'descripcion' => 'Costo por litro x cantidad de litros que tenga el auto.',
                'tipo_cobro'  => 'por_evento',
                'precio'      => 20.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Gasolina (faltante)',
                'descripcion' => 'Se cobra cuando el cliente regresa el auto con faltante de gasolina.',
                'tipo_cobro'  => 'por_evento',
                'precio'      => 25.50,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Servicio de Litro Faltante',
                'descripcion' => 'Costo aplicado a cada litro faltante al regresar el auto.',
                'tipo_cobro'  => 'por_evento',
                'precio'      => 13.16,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // =======================
            // SERVICIOS ADICIONALES
            // =======================
            [
                'nombre'      => 'Conductor adicional',
                'descripcion' => 'Agregar un conductor extra.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 150.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Conductor menor',
                'descripcion' => 'Cobro adicional por conductor menor de edad.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 200.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Licencia vencida',
                'descripcion' => 'Aplicable cuando el cliente presenta licencia vencida.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 200.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Silla de bebé',
                'descripcion' => 'Silla de seguridad para bebé.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 150.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'GPS',
                'descripcion' => 'Dispositivo GPS.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 200.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Upgrade de categoría',
                'descripcion' => 'Cambio a categoría superior.',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 200.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nombre'      => 'Accesorios de celular',
                'descripcion' => 'Accesorios para celular (cobro por día).',
                'tipo_cobro'  => 'por_dia',
                'precio'      => 150.00,
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        // Inserta o actualiza por unique(nombre)
        foreach ($rows as $row) {
            DB::table('servicios')->updateOrInsert(
                ['nombre' => $row['nombre']],
                $row
            );
        }
    }
}
