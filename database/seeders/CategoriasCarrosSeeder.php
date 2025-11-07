<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasCarrosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias_carros')->insert([
            [
                'nombre' => 'Compacto',
                'descripcion' => 'Chevrolet Aveo o similar. Autos similares: VW Virtus, Kia Rio, Hyundai HB20, Toyota Yaris.',
                'precio_dia' => 467.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Medianos',
                'descripcion' => 'Nissan Versa o similar. Autos similares: VW Virtus, Kia Rio, Hyundai HB20, Toyota Yaris.',
                'precio_dia' => 600.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Grandes',
                'descripcion' => 'Jetta o similar. Autos similares: Nissan Sentra, Toyota Corolla, Kia K4.',
                'precio_dia' => 800.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Full Size',
                'descripcion' => 'Toyota Camry o similar.',
                'precio_dia' => 1550.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'SUV Compacta',
                'descripcion' => 'Jeep Renegade o similar. Autos similares: Kia Seltos, Nissan Xtrail.',
                'precio_dia' => 1600.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'SUV Mediana',
                'descripcion' => 'Chevrolet Captiva o similar. Autos similares: Kia Seltos, Nissan Xtrail.',
                'precio_dia' => 1800.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'SUV Familiar Compacta',
                'descripcion' => 'Toyota Avanza o similar. Autos similares: Suzuki Ertiga, Mitsubishi Xpander.',
                'precio_dia' => 1700.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Minivan',
                'descripcion' => 'Honda Odyssey o similar. Autos similares: Toyota Sienna.',
                'precio_dia' => 2600.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Pasajeros 12-15',
                'descripcion' => 'Toyota Hiace o similar. Autos similares: Nissan Urvan.',
                'precio_dia' => 2900.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Pick Up Doble Cabina',
                'descripcion' => 'Nissan Frontier o similar. Autos similares: Toyota Hilux, Mitsubishi L200.',
                'precio_dia' => 1950.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Pick Up 4x4 Doble Cabina',
                'descripcion' => 'Toyota Tacoma o similar. Autos similares: No aplica.',
                'precio_dia' => 2600.00,
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }
}
