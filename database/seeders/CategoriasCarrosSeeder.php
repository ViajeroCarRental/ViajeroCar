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
                'nombre' => 'Económico',
                'descripcion' => 'Autos pequeños, eficientes y cómodos para ciudad.',
                'precio_dia' => 499.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Compacto',
                'descripcion' => 'Modelos prácticos, cómodos y con buen rendimiento.',
                'precio_dia' => 579.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Sedán',
                'descripcion' => 'Espacio para familia o trabajo con cajuela amplia.',
                'precio_dia' => 699.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'SUV',
                'descripcion' => 'Camionetas con espacio extra y tracción elevada.',
                'precio_dia' => 999.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Pickup',
                'descripcion' => 'Camionetas resistentes para carga y uso rudo.',
                'precio_dia' => 1299.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'De lujo',
                'descripcion' => 'Vehículos de alta gama con interiores premium.',
                'precio_dia' => 1599.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Deportivos',
                'descripcion' => 'Autos potentes, veloces y de alto rendimiento.',
                'precio_dia' => 1699.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Híbridos',
                'descripcion' => 'Vehículos con motor eléctrico y bajo consumo.',
                'precio_dia' => 1099.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Eléctricos',
                'descripcion' => '100% eléctricos, sin emisiones y silenciosos.',
                'precio_dia' => 1799.00,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Vans',
                'descripcion' => 'Vehículos amplios para grupos o transporte turístico.',
                'precio_dia' => 1599.00,
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }
}
