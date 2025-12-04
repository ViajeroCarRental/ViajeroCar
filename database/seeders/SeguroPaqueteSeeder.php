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
                'nombre' => 'LDW PACK',
                'descripcion' =>
"-El cliente es Responsable por el 0% deducible, de lado a lado pase lo que pase con el auto esta cubierto de bumper a bumper.
-Gastos médicos cubiertos 250,000 MXN por evento
-Asistencia en carretera Premium. Incluye: envío de llaves o gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente. no incluye costo de llave ni gasolina
-Tiempo perdido en taller, cubierto
-Asistencia Legal, Cubierta
-Responsabilidad civil hasta 3,000,000 MXN",
                'precio_por_dia' => 1120.00,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'PDW PACK',
                'descripcion' =>
"-Cubierta toda la carrosería AL 5%, 10% Perdida total o Robo, NO CUBRE llantas, accesorios, rines ni cristales
-Gastos médicos cubiertos 250,000 MXN por evento
-Asistencia Premium: El cliente es responsable por costos de: Grúa en caso de requerirla, corralón, envío de llaves o gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente
-Tiempo perdido en taller, cubierto
-Asistencia Legal, Cubierta
-Responsabilidad civil hasta 1,000,000 MXN",
                'precio_por_dia' => 855.00,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'CDW PACK 1',
                'descripcion' =>
"-El cliente es Responsable por el 10% Deducible en Daños, 20% Perdida total o Robo sobre valor factura
-Gastos médicos cubiertos 250,000 MXN por evento
-Asistencia Premium: El cliente es responsable por costos de: Grúa en caso de requerirla, corralón, envío de llaves o gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente
-Tiempo perdido en taller, cubierto
-Asistencia Legal, Cubierta
-Responsabilidad civil hasta 1,000,000 MXN",
                'precio_por_dia' => 730.00,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'CDW PACK 2',
                'descripcion' =>
"-El cliente es Responsable por el 20% Deducible en Daños, 30% Perdida total o Robo sobre valor factura
-Gastos médicos cubiertos 250,000 MXN por evento
-Asistencia Premium: El cliente es responsable por costos de: Grúa en caso de requerirla, corralón, envío de llaves o gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente
-Tiempo perdido en taller, cubierto
-Asistencia Legal, Cubierta
-Responsabilidad civil hasta 350,000 MXN",
                'precio_por_dia' => 555.00,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'DECLINE PROTECTIONS',
                'descripcion' =>
"-El cliente es Responsable por el 100% Deducible sobre valor factura de auto
-No cubre gastos médicos en caso de accidente
-Asistencia Premium: El cliente es responsable por costos de: Grúa en caso de requerirla, corralón, envío de llaves o gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente
-No cubre Tiempo perdido en taller
-No cubre Asistencia Legal
-Responsabilidad civil hasta 350,000 MXN",
                'precio_por_dia' => 0.00,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
