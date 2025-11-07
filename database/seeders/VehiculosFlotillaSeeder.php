<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculosFlotillaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vehiculos')->insert([
            [
                'id_ciudad' => 1,
                'id_sucursal' => 1,
                'id_categoria' => 1,
                'id_estatus' => 1,
                'id_marca' => 1,
                'id_modelo' => 1,
                'id_version' => null,
                'marca' => 'Nissan',
                'modelo' => 'Versa',
                'anio' => 2023,
                'nombre_publico' => 'Nissan Versa 2023 Automático',
                'transmision' => 'Automática',
                'combustible' => 'Gasolina',
                'color' => 'Blanco',
                'asientos' => 5,
                'puertas' => 4,
                'kilometraje' => 22500,
                'precio_dia' => 950.00,
                'deposito_garantia' => 5000.00,
                'placa' => 'ABC-123',
                'numero_serie' => 'NVX-001-9876',
                'descripcion' => 'Sedán económico, ideal para ciudad.',
                'categoria' => 'Medianos (Categoría D)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_ciudad' => 1,
                'id_sucursal' => 1,
                'id_categoria' => 2,
                'id_estatus' => 1,
                'id_marca' => 2,
                'id_modelo' => 2,
                'id_version' => null,
                'marca' => 'Tesla',
                'modelo' => 'Model 3',
                'anio' => 2024,
                'nombre_publico' => 'Tesla Model 3 Eléctrico 2024',
                'transmision' => 'Automática',
                'combustible' => 'Eléctrico',
                'color' => 'Rojo',
                'asientos' => 5,
                'puertas' => 4,
                'kilometraje' => 15200,
                'precio_dia' => 2500.00,
                'deposito_garantia' => 15000.00,
                'placa' => 'TES-999',
                'numero_serie' => 'TSL-002-4567',
                'descripcion' => 'Auto eléctrico premium con autonomía extendida.',
                'categoria' => 'Full size (Categoría F)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
