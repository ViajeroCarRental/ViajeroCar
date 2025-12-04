<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vehiculos')->insert([

/* ============================================================
   1) Económico — Chevrolet Aveo
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 1,
    'id_categoria' => 1,
    'id_estatus' => 1,
    'id_marca' => 1,
    'id_modelo' => 1,
    'id_version' => 1,

    'marca' => 'Chevrolet',
    'modelo' => 'Aveo',
    'numero_serie' => 'CHE-AVE-001',
    'categoria' => 'Económico',
    'anio' => 2022,
    'nombre_publico' => 'Chevrolet Aveo o similar',

    'transmision' => 'Manual',
    'combustible' => 'Gasolina',
    'color' => 'Gris Plata',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 21000,
    'gasolina_actual' => 10,

    'precio_dia' => 499.00,
    'deposito_garantia' => 1500.00,
    'placa' => 'AVE-001',
    'vin' => '1G1BC5SM2J7100001',
    'descripcion' => 'Vehículo económico con excelente rendimiento.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-AVE-001',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-12',
    'no_centro_verificacion' => 'CEN-001',
    'tipo_verificacion' => 'Ordinaria',

    'no_poliza' => 'POL-AVE-001',
    'aseguradora' => 'GNP Seguros',
    'inicio_vigencia_poliza' => '2024-01-01',
    'fin_vigencia_poliza' => '2024-12-31',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Premium',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-AVE-001',
    'movimiento_tarjeta' => 'Alta 2024',
    'fecha_expedicion_tarjeta' => '2024-01-10',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   2) Compacto — Nissan March
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 2,
    'id_categoria' => 2,
    'id_estatus' => 1,
    'id_marca' => 5,
    'id_modelo' => 5,
    'id_version' => 5,

    'marca' => 'Nissan',
    'modelo' => 'March',
    'numero_serie' => 'NIS-MAR-002',
    'categoria' => 'Compacto',
    'anio' => 2022,
    'nombre_publico' => 'Nissan March o similar',

    'transmision' => 'Manual',
    'combustible' => 'Gasolina',
    'color' => 'Rojo',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 18000,
    'gasolina_actual' => 12,

    'precio_dia' => 579.00,
    'deposito_garantia' => 1600.00,
    'placa' => 'MAR-002',
    'vin' => '3N1CK3CPXJL200002',
    'descripcion' => 'Compacto ágil y eficiente, ideal para ciudad.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-MAR-002',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-08',
    'no_centro_verificacion' => 'CEN-142',
    'tipo_verificacion' => 'Ordinaria',

    'no_poliza' => 'POL-MAR-002',
    'aseguradora' => 'AXA',
    'inicio_vigencia_poliza' => '2024-02-01',
    'fin_vigencia_poliza' => '2025-02-01',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Gold',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-MAR-002',
    'movimiento_tarjeta' => 'Alta 2023',
    'fecha_expedicion_tarjeta' => '2023-04-20',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   3) Sedán — Volkswagen Virtus
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 2,
    'id_categoria' => 3,
    'id_estatus' => 1,
    'id_marca' => 2,
    'id_modelo' => 2,
    'id_version' => 2,

    'marca' => 'Volkswagen',
    'modelo' => 'Virtus',
    'numero_serie' => 'VW-VRT-003',
    'categoria' => 'Sedán',
    'anio' => 2023,
    'nombre_publico' => 'Volkswagen Virtus o similar',

    'transmision' => 'Automático',
    'combustible' => 'Gasolina',
    'color' => 'Azul Marino',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 15000,
    'gasolina_actual' => 8,

    'precio_dia' => 699.00,
    'deposito_garantia' => 2000.00,
    'placa' => 'VRT-003',
    'vin' => 'WVW12345678900003',
    'descripcion' => 'Sedán moderno, ideal para viajes de negocio.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-VRT-003',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-06',
    'no_centro_verificacion' => 'CEN-022',
    'tipo_verificacion' => 'Ordinaria',

    'no_poliza' => 'POL-VRT-003',
    'aseguradora' => 'GNP',
    'inicio_vigencia_poliza' => '2024-03-01',
    'fin_vigencia_poliza' => '2025-03-01',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Plus',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-VRT-003',
    'movimiento_tarjeta' => 'Alta 2023',
    'fecha_expedicion_tarjeta' => '2023-03-15',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   4) SUV — Kia Sportage
============================================================ */
[
    'id_ciudad' => 2,
    'id_sucursal' => 3,
    'id_categoria' => 4,
    'id_estatus' => 1,
    'id_marca' => 3,
    'id_modelo' => 3,
    'id_version' => 3,

    'marca' => 'Kia',
    'modelo' => 'Sportage',
    'numero_serie' => 'KIA-SPT-004',
    'categoria' => 'SUV',
    'anio' => 2021,
    'nombre_publico' => 'Kia Sportage o similar',

    'transmision' => 'Automático',
    'combustible' => 'Gasolina',
    'color' => 'Blanco Perla',
    'asientos' => 5,
    'puertas' => 5,
    'kilometraje' => 30000,
    'gasolina_actual' => 6,

    'precio_dia' => 999.00,
    'deposito_garantia' => 2500.00,
    'placa' => 'SPT-004',
    'vin' => 'KNDPY123456789004',
    'descripcion' => 'SUV cómoda para viajes largos.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'San Miguel de Allende',
    'municipio' => 'Allende',
    'estado' => 'Guanajuato',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-SPT-004',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-07',
    'no_centro_verificacion' => 'CEN-311',
    'tipo_verificacion' => 'Ordinaria',

    'no_poliza' => 'POL-SPT-004',
    'aseguradora' => 'Qualitas',
    'inicio_vigencia_poliza' => '2024-04-01',
    'fin_vigencia_poliza' => '2025-04-01',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Elite',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-SPT-004',
    'movimiento_tarjeta' => 'Alta 2022',
    'fecha_expedicion_tarjeta' => '2022-05-10',
    'oficina_expedidora' => 'León SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   5) Pickup — Ford Ranger
============================================================ */
[
    'id_ciudad' => 2,
    'id_sucursal' => 3,
    'id_categoria' => 5,
    'id_estatus' => 1,
    'id_marca' => 7,
    'id_modelo' => 7,
    'id_version' => 7,

    'marca' => 'Ford',
    'modelo' => 'Ranger',
    'numero_serie' => 'FOR-RAN-005',
    'categoria' => 'Pickup',
    'anio' => 2021,
    'nombre_publico' => 'Ford Ranger o similar',

    'transmision' => 'Automático',
    'combustible' => 'Diésel',
    'color' => 'Gris Oxford',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 42000,
    'gasolina_actual' => 7,

    'precio_dia' => 1299.00,
    'deposito_garantia' => 3000.00,
    'placa' => 'RAN-005',
    'vin' => '1FTYR10E45PA00005',
    'descripcion' => 'Pickup doble cabina, tracción total.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'San Miguel de Allende',
    'municipio' => 'Allende',
    'estado' => 'Guanajuato',
    'pais' => 'México',

    'cilindros' => 6,
    'numero_motor' => 'MOT-RAN-005',
    'holograma' => '1',
    'vigencia_verificacion' => '2025-10',
    'no_centro_verificacion' => 'CEN-511',
    'tipo_verificacion' => 'Diesel',

    'no_poliza' => 'POL-RAN-005',
    'aseguradora' => 'AXA',
    'inicio_vigencia_poliza' => '2024-03-15',
    'fin_vigencia_poliza' => '2025-03-15',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Work',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-RAN-005',
    'movimiento_tarjeta' => 'Alta 2022',
    'fecha_expedicion_tarjeta' => '2022-07-01',
    'oficina_expedidora' => 'León SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   6) De lujo — BMW Serie 3
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 1,
    'id_categoria' => 6,
    'id_estatus' => 1,
    'id_marca' => 4,
    'id_modelo' => 4,
    'id_version' => 4,

    'marca' => 'BMW',
    'modelo' => 'Serie 3',
    'numero_serie' => 'BMW-320-006',
    'categoria' => 'De lujo',
    'anio' => 2023,
    'nombre_publico' => 'BMW Serie 3 o similar',

    'transmision' => 'Automático',
    'combustible' => 'Gasolina Premium',
    'color' => 'Negro',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 8000,
    'gasolina_actual' => 14,

    'precio_dia' => 1599.00,
    'deposito_garantia' => 5000.00,
    'placa' => 'BMW-006',
    'vin' => 'WBA8E9C55GK000006',
    'descripcion' => 'Sedán premium con desempeño superior.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-BMW-006',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-11',
    'no_centro_verificacion' => 'CEN-901',
    'tipo_verificacion' => 'Ordinaria',

    'no_poliza' => 'POL-BMW-006',
    'aseguradora' => 'GNP',
    'inicio_vigencia_poliza' => '2024-05-10',
    'fin_vigencia_poliza' => '2025-05-10',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Diamond',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-BMW-006',
    'movimiento_tarjeta' => 'Alta 2023',
    'fecha_expedicion_tarjeta' => '2023-06-05',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   7) Deportivo — Mazda MX-5
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 1,
    'id_categoria' => 7,
    'id_estatus' => 1,
    'id_marca' => 10,
    'id_modelo' => 10,
    'id_version' => 10,

    'marca' => 'Mazda',
    'modelo' => 'MX-5',
    'numero_serie' => 'MAZ-MX5-007',
    'categoria' => 'Deportivo',
    'anio' => 2022,
    'nombre_publico' => 'Mazda MX-5 o similar',

    'transmision' => 'Manual',
    'combustible' => 'Gasolina',
    'color' => 'Rojo Soul',
    'asientos' => 2,
    'puertas' => 2,
    'kilometraje' => 12000,
    'gasolina_actual' => 11,

    'precio_dia' => 1699.00,
    'deposito_garantia' => 4000.00,
    'placa' => 'MX5-007',
    'vin' => 'JM1NDAD79G010007',
    'descripcion' => 'Roadster ligero ideal para escapadas de fin de semana.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-MX5-007',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-10',
    'no_centro_verificacion' => 'CEN-117',
    'tipo_verificacion' => 'Ordinaria',

    'no_poliza' => 'POL-MX5-007',
    'aseguradora' => 'AXA',
    'inicio_vigencia_poliza' => '2024-07-01',
    'fin_vigencia_poliza' => '2025-07-01',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Sport',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-MX5-007',
    'movimiento_tarjeta' => 'Alta 2022',
    'fecha_expedicion_tarjeta' => '2022-08-12',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   8) Híbrido — Toyota Prius
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 2,
    'id_categoria' => 8,
    'id_estatus' => 1,
    'id_marca' => 6,
    'id_modelo' => 6,
    'id_version' => 6,

    'marca' => 'Toyota',
    'modelo' => 'Prius',
    'numero_serie' => 'TOY-PRI-008',
    'categoria' => 'Híbrido',
    'anio' => 2022,
    'nombre_publico' => 'Toyota Prius o similar',

    'transmision' => 'Automático',
    'combustible' => 'Híbrido',
    'color' => 'Blanco',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 25000,
    'gasolina_actual' => 9,

    'precio_dia' => 1099.00,
    'deposito_garantia' => 3000.00,
    'placa' => 'PRI-008',
    'vin' => 'JTDKBRFU0H3000008',
    'descripcion' => 'Híbrido eficiente ideal para viajes largos.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-PRI-008',
    'holograma' => '00',
    'vigencia_verificacion' => '2025-08',
    'no_centro_verificacion' => 'CEN-211',
    'tipo_verificacion' => 'Híbrida',

    'no_poliza' => 'POL-PRI-008',
    'aseguradora' => 'Qualitas',
    'inicio_vigencia_poliza' => '2024-04-05',
    'fin_vigencia_poliza' => '2025-04-05',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Eco',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-PRI-008',
    'movimiento_tarjeta' => 'Alta 2022',
    'fecha_expedicion_tarjeta' => '2022-05-11',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   9) Eléctrico — Tesla Model 3
============================================================ */
[
    'id_ciudad' => 1,
    'id_sucursal' => 1,
    'id_categoria' => 9,
    'id_estatus' => 1,
    'id_marca' => 8,
    'id_modelo' => 8,
    'id_version' => 8,

    'marca' => 'Tesla',
    'modelo' => 'Model 3',
    'numero_serie' => 'TSL-MOD3-009',
    'categoria' => 'Eléctrico',
    'anio' => 2023,
    'nombre_publico' => 'Tesla Model 3 o similar',

    'transmision' => 'Automático',
    'combustible' => 'Eléctrico',
    'color' => 'Blanco Multicapa',
    'asientos' => 5,
    'puertas' => 4,
    'kilometraje' => 5000,
    'gasolina_actual' => 0,

    'precio_dia' => 1799.00,
    'deposito_garantia' => 6000.00,
    'placa' => 'TES-009',
    'vin' => '5YJ3E1EA7PF000009',
    'descripcion' => '100% eléctrico, silencioso y autónomo.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'Querétaro, Qro.',
    'municipio' => 'Querétaro',
    'estado' => 'Querétaro',
    'pais' => 'México',

    'cilindros' => null,
    'numero_motor' => 'ELEC-TES-009',
    'holograma' => 'Exento',
    'vigencia_verificacion' => null,
    'no_centro_verificacion' => null,
    'tipo_verificacion' => null,

    'no_poliza' => 'POL-TES-009',
    'aseguradora' => 'GNP',
    'inicio_vigencia_poliza' => '2024-06-01',
    'fin_vigencia_poliza' => '2025-06-01',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Electric Premium',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-TES-009',
    'movimiento_tarjeta' => 'Alta 2023',
    'fecha_expedicion_tarjeta' => '2023-06-20',
    'oficina_expedidora' => 'Querétaro SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

/* ============================================================
   10) Van — Mercedes-Benz Clase V
============================================================ */
[
    'id_ciudad' => 2,
    'id_sucursal' => 3,
    'id_categoria' => 10,
    'id_estatus' => 1,
    'id_marca' => 9,
    'id_modelo' => 9,
    'id_version' => 9,

    'marca' => 'Mercedes-Benz',
    'modelo' => 'Clase V',
    'numero_serie' => 'MBZ-VAN-010',
    'categoria' => 'Van',
    'anio' => 2021,
    'nombre_publico' => 'Mercedes-Benz Clase V o similar',

    'transmision' => 'Automático',
    'combustible' => 'Diésel',
    'color' => 'Plata',
    'asientos' => 7,
    'puertas' => 5,
    'kilometraje' => 35000,
    'gasolina_actual' => 5,

    'precio_dia' => 1599.00,
    'deposito_garantia' => 4500.00,
    'placa' => 'VAN-010',
    'vin' => 'WDF44770313200010',
    'descripcion' => 'Van premium ideal para transporte ejecutivo.',

    'tipo_servicio' => 'Arrendamiento',
    'propietario' => 'Viajero Car Rental',
    'rfc_propietario' => 'VCR2024A12',
    'domicilio' => 'San Miguel de Allende',
    'municipio' => 'Allende',
    'estado' => 'Guanajuato',
    'pais' => 'México',

    'cilindros' => 4,
    'numero_motor' => 'MOT-MBZ-010',
    'holograma' => '1',
    'vigencia_verificacion' => '2025-03',
    'no_centro_verificacion' => 'CEN-337',
    'tipo_verificacion' => 'Diesel',

    'no_poliza' => 'POL-MBZ-010',
    'aseguradora' => 'AXA',
    'inicio_vigencia_poliza' => '2024-08-01',
    'fin_vigencia_poliza' => '2025-08-01',
    'tipo_cobertura' => 'Amplia',
    'plan_seguro' => 'Executive',
    'archivo_poliza' => null,

    'folio_tarjeta' => 'TC-MBZ-010',
    'movimiento_tarjeta' => 'Alta 2022',
    'fecha_expedicion_tarjeta' => '2022-09-10',
    'oficina_expedidora' => 'León SAT',
    'archivo_verificacion' => null,

    'created_at' => now(),
    'updated_at' => now(),
],

        ]);
    }
}
