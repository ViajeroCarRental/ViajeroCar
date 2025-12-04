<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasCarrosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias_carros')->insert([

            // C — Compacto
            [
                'codigo'        => 'C',
                'nombre'        => 'Compacto',
                'descripcion'   => 'Chevrolet Aveo o similar',
                'precio_dia'    => 467.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // D — Medianos
            [
                'codigo'        => 'D',
                'nombre'        => 'Medianos',
                'descripcion'   => 'Nissan Versa o similar',
                'precio_dia'    => 600.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // E — Grandes
            [
                'codigo'        => 'E',
                'nombre'        => 'Grandes',
                'descripcion'   => 'Volkswagen Jetta o similar',
                'precio_dia'    => 800.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // F — Full Size
            [
                'codigo'        => 'F',
                'nombre'        => 'Full Size',
                'descripcion'   => 'Toyota Camry o similar',
                'precio_dia'    => 1550.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // IC — SUV Compacta
            [
                'codigo'        => 'IC',
                'nombre'        => 'SUV Compacta',
                'descripcion'   => 'Jeep Renegade o similar',
                'precio_dia'    => 1600.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // I — SUV Mediana
            [
                'codigo'        => 'I',
                'nombre'        => 'SUV Mediana',
                'descripcion'   => 'Volkswagen Taos o similar',
                'precio_dia'    => 1800.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // IB — SUV Familiar Compacta
            [
                'codigo'        => 'IB',
                'nombre'        => 'SUV Familiar Compacta',
                'descripcion'   => 'Toyota Avanza o similar',
                'precio_dia'    => 1700.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // M — Minivan
            [
                'codigo'        => 'M',
                'nombre'        => 'Minivan',
                'descripcion'   => 'Honda Odyssey o similar',
                'precio_dia'    => 2600.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // L — Pasajeros 12–15
            [
                'codigo'        => 'L',
                'nombre'        => 'Van Pasajeros 13',
                'descripcion'   => 'Toyota Hiace o similar',
                'precio_dia'    => 2900.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // H — Pickup Doble Cabina
            [
                'codigo'        => 'H',
                'nombre'        => 'Pickup Doble Cabina',
                'descripcion'   => 'Nissan Frontier o similar',
                'precio_dia'    => 1950.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

            // HI — Pickup 4x4
            [
                'codigo'        => 'HI',
                'nombre'        => 'Pickup 4x4 Doble Cabina',
                'descripcion'   => 'Toyota Tacoma o similar',
                'precio_dia'    => 2600.00,
                'created_at'    => now(),
                'updated_at'    => now()
            ],

        ]);
    }
}
