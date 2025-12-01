<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MantenimientosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mantenimientos')->insert([

/* ============================================================
   1) Chevrolet Aveo — Económico — Servicio regular
============================================================ */
[
    'id_vehiculo' => 1,
    'id_tipo' => 1, // Servicio menor

    'kilometraje_actual' => 21000,
    'ultimo_km_servicio' => 15000,
    'intervalo_km' => 10000,
    'proximo_servicio' => 25000,
    'fecha_servicio' => '2024-02-10',
    'costo_servicio' => 950.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '5W-30 Sintético',
    'rotacion_llantas' => true,
    'cambio_filtro' => true,
    'cambio_pastillas' => false,

    'observaciones' => 'Vehículo en buen estado. Se recomienda revisar frenos en el próximo servicio.',
    'estatus' => 'verde',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   2) Nissan March — Compacto
============================================================ */
[
    'id_vehiculo' => 2,
    'id_tipo' => 1,

    'kilometraje_actual' => 18000,
    'ultimo_km_servicio' => 10000,
    'intervalo_km' => 10000,
    'proximo_servicio' => 20000,
    'fecha_servicio' => '2024-01-25',
    'costo_servicio' => 880.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '5W-30',
    'rotacion_llantas' => false,
    'cambio_filtro' => true,
    'cambio_pastillas' => false,

    'observaciones' => 'Próximo servicio cercano.',
    'estatus' => 'amarillo',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   3) Volkswagen Virtus — Sedán
============================================================ */
[
    'id_vehiculo' => 3,
    'id_tipo' => 2, // Servicio intermedio

    'kilometraje_actual' => 15000,
    'ultimo_km_servicio' => 8000,
    'intervalo_km' => 10000,
    'proximo_servicio' => 18000,
    'fecha_servicio' => '2024-03-01',
    'costo_servicio' => 1400.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '0W-20 Sintético',
    'rotacion_llantas' => true,
    'cambio_filtro' => true,
    'cambio_pastillas' => false,

    'observaciones' => 'Se realizó inspección general.',
    'estatus' => 'verde',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   4) Kia Sportage — SUV
============================================================ */
[
    'id_vehiculo' => 4,
    'id_tipo' => 2,

    'kilometraje_actual' => 30000,
    'ultimo_km_servicio' => 20000,
    'intervalo_km' => 10000,
    'proximo_servicio' => 30000,
    'fecha_servicio' => '2024-03-18',
    'costo_servicio' => 2200.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '5W-40 Full Sintético',
    'rotacion_llantas' => true,
    'cambio_filtro' => true,
    'cambio_pastillas' => true,

    'observaciones' => 'Se cambiaron pastillas delanteras.',
    'estatus' => 'rojo', // KM límite alcanzado

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   5) Ford Ranger — Pickup Diesel
============================================================ */
[
    'id_vehiculo' => 5,
    'id_tipo' => 3, // Servicio mayor

    'kilometraje_actual' => 42000,
    'ultimo_km_servicio' => 30000,
    'intervalo_km' => 15000,
    'proximo_servicio' => 45000,
    'fecha_servicio' => '2024-02-28',
    'costo_servicio' => 3500.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '15W-40 para Diésel',
    'rotacion_llantas' => true,
    'cambio_filtro' => true,
    'cambio_pastillas' => true,

    'observaciones' => 'Se revisó suspensión y líneas de freno.',
    'estatus' => 'verde',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   6) BMW Serie 3 — Lujo
============================================================ */
[
    'id_vehiculo' => 6,
    'id_tipo' => 2,

    'kilometraje_actual' => 8000,
    'ultimo_km_servicio' => 0,
    'intervalo_km' => 15000,
    'proximo_servicio' => 15000,
    'fecha_servicio' => '2024-04-01',
    'costo_servicio' => 2800.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '0W-30 Sintético Premium',
    'rotacion_llantas' => false,
    'cambio_filtro' => true,
    'cambio_pastillas' => false,

    'observaciones' => 'Primer servicio.',
    'estatus' => 'verde',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   7) Mazda MX-5 — Deportivo
============================================================ */
[
    'id_vehiculo' => 7,
    'id_tipo' => 2,

    'kilometraje_actual' => 12000,
    'ultimo_km_servicio' => 6000,
    'intervalo_km' => 10000,
    'proximo_servicio' => 16000,
    'fecha_servicio' => '2024-02-20',
    'costo_servicio' => 1850.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '0W-20 Sintético',
    'rotacion_llantas' => false,
    'cambio_filtro' => true,
    'cambio_pastillas' => false,

    'observaciones' => 'Se revisó desgaste de embrague.',
    'estatus' => 'verde',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   8) Toyota Prius — Híbrido
============================================================ */
[
    'id_vehiculo' => 8,
    'id_tipo' => 1,

    'kilometraje_actual' => 25000,
    'ultimo_km_servicio' => 15000,
    'intervalo_km' => 10000,
    'proximo_servicio' => 25000,
    'fecha_servicio' => '2024-02-14',
    'costo_servicio' => 1300.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '0W-20 Sintético',
    'rotacion_llantas' => true,
    'cambio_filtro' => true,
    'cambio_pastillas' => false,

    'observaciones' => 'Revisión de batería híbrida sin anomalías.',
    'estatus' => 'rojo',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   9) Tesla Model 3 — 100% Eléctrico
============================================================ */
[
    'id_vehiculo' => 9,
    'id_tipo' => 4, // Eléctrico

    'kilometraje_actual' => 5000,
    'ultimo_km_servicio' => 0,
    'intervalo_km' => 20000,
    'proximo_servicio' => 20000,
    'fecha_servicio' => '2024-03-10',
    'costo_servicio' => 850.00,

    'cambio_aceite' => false,
    'tipo_aceite' => null,
    'rotacion_llantas' => true,
    'cambio_filtro' => false,
    'cambio_pastillas' => false,

    'observaciones' => 'Revisión de batería y frenos regenerativos.',
    'estatus' => 'verde',

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   10) Mercedes-Benz Clase V — Van
============================================================ */
[
    'id_vehiculo' => 10,
    'id_tipo' => 3,

    'kilometraje_actual' => 35000,
    'ultimo_km_servicio' => 25000,
    'intervalo_km' => 15000,
    'proximo_servicio' => 40000,
    'fecha_servicio' => '2024-02-01',
    'costo_servicio' => 3200.00,

    'cambio_aceite' => true,
    'tipo_aceite' => '5W-40',
    'rotacion_llantas' => true,
    'cambio_filtro' => true,
    'cambio_pastillas' => true,

    'observaciones' => 'Se realizó servicio mayor a motor y suspensión.',
    'estatus' => 'amarillo',

    'created_at' => now(),
    'updated_at' => now(),
],

        ]);
    }
}
