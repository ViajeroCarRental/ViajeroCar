<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeguroIndividualesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('seguro_individuales')->insert([

            /* =====================================================
               COLISIÓN Y ROBO (5)
            ====================================================== */
            [
                'nombre' => 'LDW (Loss Damage Waiver)',
                'descripcion' => '0% deducible. El cliente es responsable por el 0% deducible, esté o no esté presente, de lado a lado, pase lo que pase con el auto.',
                'precio_por_dia' => 675.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'PDW (Provisional Damage Waiver)',
                'descripcion' => 'Cubre carrocería al 5%. 10% pérdida total o robo. No cubre llantas, accesorios, rines ni cristales.',
                'precio_por_dia' => 575.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CDW (Collision Damage Waiver) 10% Deducible',
                'descripcion' => '10% deducible en daños. 20% pérdida total o robo sobre valor factura.',
                'precio_por_dia' => 450.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CDW (Collision Damage Waiver) 20% Deducible',
                'descripcion' => '20% deducible en daños. 30% pérdida total o robo sobre valor factura.',
                'precio_por_dia' => 375.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'DECLINE CDW',
                'descripcion' => 'El cliente es responsable por el 100% deducible sobre valor factura del auto.',
                'precio_por_dia' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            /* =====================================================
               GASTOS MÉDICOS (1)
            ====================================================== */
            [
                'nombre' => 'PAI (Personal Accident Insurance)',
                'descripcion' => 'Gastos médicos cubiertos hasta 250,000 MXN por evento.',
                'precio_por_dia' => 180.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            /* =====================================================
               ASISTENCIA PARA EL CAMINO (1)
            ====================================================== */
            [
                'nombre' => 'PRA (Premium Road Assistant)',
                'descripcion' => 'Asistencia en carretera Premium: llaves, gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente. No incluye costo de llave ni gasolina.',
                'precio_por_dia' => 75.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            /* =====================================================
               DAÑOS A TERCEROS (3)
            ====================================================== */
            [
                'nombre' => 'LI (Liability Insurance)',
                'descripcion' => 'Responsabilidad civil hasta 350,000 MXN.',
                'precio_por_dia' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'ALI (Additional Liability Insurance)',
                'descripcion' => 'Responsabilidad civil hasta 1,000,000 MXN.',
                'precio_por_dia' => 100.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'EXT. LI (Extension Liability Insurance)',
                'descripcion' => 'Responsabilidad civil hasta 3,000,000 MXN.',
                'precio_por_dia' => 190.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            /* =====================================================
               PROTECCIONES AUTOMÁTICAS (2)
            ====================================================== */
            [
                'nombre' => 'LOU (Loss of Use)',
                'descripcion' => 'Tiempo perdido en taller, cubierto.',
                'precio_por_dia' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'LA (Legal Assistance)',
                'descripcion' => 'Asistencia legal, cubierta.',
                'precio_por_dia' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
