<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VersionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('versiones')->insert([
            // 1. Chevrolet Aveo (Económico)
            [
                'id_modelo' => 1,
                'nombre' => 'LS Sedan',
                'descripcion' => 'Versión básica económica con transmisión manual.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. Volkswagen Virtus (Sedán)
            [
                'id_modelo' => 2,
                'nombre' => 'Comfortline',
                'descripcion' => 'Sedán con transmisión automática, ideal para ciudad.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. Kia Sportage (SUV)
            [
                'id_modelo' => 3,
                'nombre' => 'EX Pack',
                'descripcion' => 'SUV moderna, cómoda y eficiente con A/C.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 4. BMW Serie 3 (De lujo)
            [
                'id_modelo' => 4,
                'nombre' => '320i',
                'descripcion' => 'Versión deportiva con acabados premium.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 5. Nissan March (Compacto)
            [
                'id_modelo' => 5,
                'nombre' => 'Advance',
                'descripcion' => 'Compacto urbano con gran eficiencia de combustible.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 6. Toyota Prius (Híbrido)
            [
                'id_modelo' => 6,
                'nombre' => 'Premium Hybrid',
                'descripcion' => 'Híbrido con motor eficiente y sistema eléctrico.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 7. Ford Ranger (Pickup)
            [
                'id_modelo' => 7,
                'nombre' => 'XLT 4x4',
                'descripcion' => 'Pickup doble cabina con potencia y tracción total.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 8. Tesla Model 3 (Eléctrico)
            [
                'id_modelo' => 8,
                'nombre' => 'Long Range',
                'descripcion' => '100% eléctrico, con gran autonomía y carga rápida.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 9. Mercedes-Benz Clase V (Vans)
            [
                'id_modelo' => 9,
                'nombre' => 'Avantgarde',
                'descripcion' => 'Van premium con espacio para 7 pasajeros.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 10. Mazda MX-5 (Deportivo)
            [
                'id_modelo' => 10,
                'nombre' => 'RF Skyactiv',
                'descripcion' => 'Roadster deportivo de alto rendimiento.',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
