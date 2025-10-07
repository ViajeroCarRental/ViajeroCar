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
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Compacto',
                'descripcion' => 'Modelos prácticos, cómodos y con buen rendimiento.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Sedán',
                'descripcion' => 'Espacio para familia o trabajo con cajuela amplia.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'SUV',
                'descripcion' => 'Camionetas con espacio extra y tracción elevada.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Pickup',
                'descripcion' => 'Camionetas resistentes para carga y uso rudo.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'De lujo',
                'descripcion' => 'Vehículos de alta gama con interiores premium.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Deportivos',
                'descripcion' => 'Autos potentes, veloces y de alto rendimiento.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Híbridos',
                'descripcion' => 'Vehículos con motor eléctrico y bajo consumo.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Eléctricos',
                'descripcion' => '100% eléctricos, sin emisiones y silenciosos.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Vans',
                'descripcion' => 'Vehículos amplios para grupos o transporte turístico.',
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }
}
