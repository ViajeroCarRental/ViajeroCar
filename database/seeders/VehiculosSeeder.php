<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vehiculos')->insert([
            // 1) Económico — Chevrolet Aveo
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 1,
                'id_categoria'   => 1, // Económico
                'id_estatus'     => 1, // Disponible
                'id_marca'       => 1, // Chevrolet
                'id_modelo'      => 1, // Aveo
                'id_version'     => 1, // LS Sedan
                'marca'          => 'Chevrolet',
                'modelo'         => 'Aveo',
                'anio'           => 2022,
                'nombre_publico' => 'Chevrolet Aveo o similar',
                'transmision'    => 'Manual',
                'combustible'    => 'Gasolina',
                'color'          => 'Gris Plata',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 21000,
                'precio_dia'     => 499.00,
                'deposito_garantia' => 1500.00,
                'descripcion'    => 'Vehículo económico, cómodo y con excelente rendimiento de combustible.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 2) Compacto — Nissan March
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 2,
                'id_categoria'   => 2, // Compacto
                'id_estatus'     => 1,
                'id_marca'       => 5, // Nissan
                'id_modelo'      => 5, // March
                'id_version'     => 5, // Advance
                'marca'          => 'Nissan',
                'modelo'         => 'March',
                'anio'           => 2022,
                'nombre_publico' => 'Nissan March o similar',
                'transmision'    => 'Manual',
                'combustible'    => 'Gasolina',
                'color'          => 'Rojo',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 18000,
                'precio_dia'     => 579.00,
                'deposito_garantia' => 1600.00,
                'descripcion'    => 'Compacto ágil y eficiente, perfecto para ciudad.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 3) Sedán — Volkswagen Virtus
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 2,
                'id_categoria'   => 3, // Sedán
                'id_estatus'     => 1,
                'id_marca'       => 2, // Volkswagen
                'id_modelo'      => 2, // Virtus
                'id_version'     => 2, // Comfortline
                'marca'          => 'Volkswagen',
                'modelo'         => 'Virtus',
                'anio'           => 2023,
                'nombre_publico' => 'Volkswagen Virtus o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Gasolina',
                'color'          => 'Azul Marino',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 15000,
                'precio_dia'     => 699.00,
                'deposito_garantia' => 2000.00,
                'descripcion'    => 'Sedán cómodo y moderno, ideal para viajes familiares o de negocios.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 4) SUV — Kia Sportage
            [
                'id_ciudad'      => 2,
                'id_sucursal'    => 3,
                'id_categoria'   => 4, // SUV
                'id_estatus'     => 1,
                'id_marca'       => 3, // Kia
                'id_modelo'      => 3, // Sportage
                'id_version'     => 3, // EX Pack
                'marca'          => 'Kia',
                'modelo'         => 'Sportage',
                'anio'           => 2021,
                'nombre_publico' => 'Kia Sportage o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Gasolina',
                'color'          => 'Blanco Perla',
                'asientos'       => 5,
                'puertas'        => 5,
                'kilometraje'    => 30000,
                'precio_dia'     => 999.00,
                'deposito_garantia' => 2500.00,
                'descripcion'    => 'SUV espacioso, con gran desempeño y confort para viajes largos.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 5) Pickup — Ford Ranger
            [
                'id_ciudad'      => 2,
                'id_sucursal'    => 3,
                'id_categoria'   => 5, // Pickup
                'id_estatus'     => 1,
                'id_marca'       => 7, // Ford
                'id_modelo'      => 7, // Ranger
                'id_version'     => 7, // XLT 4x4
                'marca'          => 'Ford',
                'modelo'         => 'Ranger',
                'anio'           => 2021,
                'nombre_publico' => 'Ford Ranger o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Diésel',
                'color'          => 'Gris Oxford',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 42000,
                'precio_dia'     => 1299.00,
                'deposito_garantia' => 3000.00,
                'descripcion'    => 'Pickup doble cabina con potencia y tracción total.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 6) De lujo — BMW Serie 3
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 1,
                'id_categoria'   => 6, // De lujo
                'id_estatus'     => 1,
                'id_marca'       => 4, // BMW
                'id_modelo'      => 4, // Serie 3
                'id_version'     => 4, // 320i
                'marca'          => 'BMW',
                'modelo'         => 'Serie 3',
                'anio'           => 2023,
                'nombre_publico' => 'BMW Serie 3 o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Gasolina Premium',
                'color'          => 'Negro',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 8000,
                'precio_dia'     => 1.00,
                'deposito_garantia' => 5000.00,
                'descripcion'    => 'Lujo y desempeño con tecnología avanzada y máxima seguridad.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 7) Deportivos — Mazda MX-5
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 1,
                'id_categoria'   => 7, // Deportivos
                'id_estatus'     => 1,
                'id_marca'       => 10, // Mazda
                'id_modelo'      => 10, // MX-5
                'id_version'     => 10, // RF Skyactiv
                'marca'          => 'Mazda',
                'modelo'         => 'MX-5',
                'anio'           => 2022,
                'nombre_publico' => 'Mazda MX-5 o similar',
                'transmision'    => 'Manual',
                'combustible'    => 'Gasolina',
                'color'          => 'Rojo Soul',
                'asientos'       => 2,
                'puertas'        => 2,
                'kilometraje'    => 12000,
                'precio_dia'     => 1699.00,
                'deposito_garantia' => 4000.00,
                'descripcion'    => 'Roadster ligero y divertido, ideal para escapadas.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 8) Híbridos — Toyota Prius
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 2,
                'id_categoria'   => 8, // Híbridos
                'id_estatus'     => 1,
                'id_marca'       => 6, // Toyota
                'id_modelo'      => 6, // Prius
                'id_version'     => 6, // Premium Hybrid
                'marca'          => 'Toyota',
                'modelo'         => 'Prius',
                'anio'           => 2022,
                'nombre_publico' => 'Toyota Prius o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Híbrido',
                'color'          => 'Blanco',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 25000,
                'precio_dia'     => 1099.00,
                'deposito_garantia' => 3000.00,
                'descripcion'    => 'Híbrido altamente eficiente, ideal para viajes largos.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 9) Eléctricos — Tesla Model 3
            [
                'id_ciudad'      => 1,
                'id_sucursal'    => 1,
                'id_categoria'   => 9, // Eléctricos
                'id_estatus'     => 1,
                'id_marca'       => 8, // Tesla
                'id_modelo'      => 8, // Model 3
                'id_version'     => 8, // Long Range
                'marca'          => 'Tesla',
                'modelo'         => 'Model 3',
                'anio'           => 2023,
                'nombre_publico' => 'Tesla Model 3 o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Eléctrico',
                'color'          => 'Perla Blanca Multicapa',
                'asientos'       => 5,
                'puertas'        => 4,
                'kilometraje'    => 5000,
                'precio_dia'     => 1799.00,
                'deposito_garantia' => 6000.00,
                'descripcion'    => '100% eléctrico, silencioso y con gran autonomía.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],

            // 10) Vans — Mercedes-Benz Clase V
            [
                'id_ciudad'      => 2,
                'id_sucursal'    => 3,
                'id_categoria'   => 10, // Vans
                'id_estatus'     => 1,
                'id_marca'       => 9, // Mercedes-Benz
                'id_modelo'      => 9, // Clase V
                'id_version'     => 9, // Avantgarde
                'marca'          => 'Mercedes-Benz',
                'modelo'         => 'Clase V',
                'anio'           => 2021,
                'nombre_publico' => 'Mercedes-Benz Clase V o similar',
                'transmision'    => 'Automático',
                'combustible'    => 'Diésel',
                'color'          => 'Plata',
                'asientos'       => 7,
                'puertas'        => 5,
                'kilometraje'    => 35000,
                'precio_dia'     => 1599.00,
                'deposito_garantia' => 4500.00,
                'descripcion'    => 'Van premium, ideal para grupos o transporte ejecutivo.',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
