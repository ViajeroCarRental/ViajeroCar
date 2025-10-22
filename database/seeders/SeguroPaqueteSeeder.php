<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeguroPaqueteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('seguro_paquete')->insert([
            [
                'nombre' => 'LDW PACK (Full Cover)',
                'descripcion' => 'Cobertura total sin deducible. Incluye:
                    - LDW (Loss Damage Waiver): El cliente no paga deducible (0%).
                    - PAI (Gastos médicos hasta 250,000 MXN por evento).
                    - PRA (Asistencia vial Premium: grúa, envío de gasolina, cambio de neumático, paso de corriente).
                    - LOU (Tiempo perdido en taller: cubierto).
                    - LA (Asistencia legal: incluida).
                    - EXT.LI (Responsabilidad civil hasta 3,000,000 MXN).
                    Depósito en garantía depende de la categoría del vehículo.',
                'precio_por_dia' => 675.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'PDW PACK (Amplia)',
                'descripcion' => 'Cobertura amplia con deducible del 5%. Incluye:
                    - PDW (Provisional Damage Waiver): Cubre carrocería al 5%, no cubre llantas, rines ni cristales.
                    - PAI (Gastos médicos hasta 250,000 MXN por evento).
                    - LOU (Tiempo perdido en taller: incluido).
                    - LA (Asistencia legal: incluida).
                    - ALI (Responsabilidad civil hasta 1,000,000 MXN).
                    - PRA (Asistencia Premium: no incluida).
                    Depósito en garantía depende de la categoría del vehículo.',
                'precio_por_dia' => 575.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CDW PACK 1 (10% Deducible)',
                'descripcion' => 'Cobertura con deducible del 10%. Incluye:
                    - CDW (Collision Damage Waiver 10%): Deducible en daños o robo total 10% del valor factura.
                    - PAI (Gastos médicos hasta 250,000 MXN por evento).
                    - LOU (Tiempo perdido en taller: incluido).
                    - LA (Asistencia legal: incluida).
                    - ALI (Responsabilidad civil hasta 1,000,000 MXN).
                    - PRA (Asistencia vial Premium: no incluida).
                    Depósito en garantía depende de la categoría del vehículo.',
                'precio_por_dia' => 450.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'CDW PACK 2 (20% Deducible)',
                'descripcion' => 'Cobertura con deducible del 20%. Incluye:
                    - CDW (Collision Damage Waiver 20%): Deducible en daños o robo total 20% del valor factura.
                    - PAI (Gastos médicos hasta 250,000 MXN por evento).
                    - LOU (Tiempo perdido en taller: incluido).
                    - LA (Asistencia legal: incluida).
                    - LI (Responsabilidad civil hasta 350,000 MXN).
                    - PRA (Asistencia vial Premium: no incluida).
                    Depósito en garantía depende de la categoría del vehículo.',
                'precio_por_dia' => 375.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'DECLINE PACK (Sin Cobertura)',
                'descripcion' => 'El cliente asume el 100% de los daños. Incluye solo:
                    - LI (Responsabilidad civil hasta 350,000 MXN).
                    No incluye cobertura médica, asistencia vial, ni protección de deducible.
                    El cliente paga el total de los daños o robo sobre el valor factura.',
                'precio_por_dia' => 0.00,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
