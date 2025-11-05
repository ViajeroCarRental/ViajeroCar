<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoConceptoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cargo_concepto')->insert([
    [
        'clave'       => 'C001',
        'nombre'      => 'Entrega en otra sucursal',
        'descripcion' => 'Cargo por devolver el vehículo en una sucursal distinta a la de retiro.',
        'monto_base'  => 800.00,
        'moneda'      => 'MXN',
        'activo'      => true,
        'created_at'  => now(),
        'updated_at'  => now(),
    ],
    [
        'clave'       => 'C002',
        'nombre'      => 'Tanque incompleto',
        'descripcion' => 'Cargo por devolver el vehículo con menos combustible del indicado.',
        'monto_base'  => 600.00,
        'moneda'      => 'MXN',
        'activo'      => true,
        'created_at'  => now(),
        'updated_at'  => now(),
    ],
    [
        'clave'       => 'C003',
        'nombre'      => 'Cambio de vehículo',
        'descripcion' => 'Costo administrativo por cambio de unidad durante el periodo de renta.',
        'monto_base'  => 500.00,
        'moneda'      => 'MXN',
        'activo'      => true,
        'created_at'  => now(),
        'updated_at'  => now(),
    ],
    [
        'clave'       => 'C004',
        'nombre'      => 'Entrega fuera de horario',
        'descripcion' => 'Costo por entregar o recoger el vehículo fuera del horario habitual.',
        'monto_base'  => 350.00,
        'moneda'      => 'MXN',
        'activo'      => true,
        'created_at'  => now(),
        'updated_at'  => now(),
    ],
]);

    }
}
