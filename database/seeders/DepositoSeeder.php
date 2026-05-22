<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepositoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla por si vuelves a ejecutar el seeder
        DB::table('depositos')->truncate();

        // Matriz financiera cruzando el "codigo" del auto con el "nombre" del seguro
        $matrizGarantias = [
            'C' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 8000.00,
                'CDW PACK 1'          => 15000.00,
                'CDW PACK 2'          => 25000.00,
                'DECLINE PROTECTIONS' => 330000.00
            ],
            'D' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 8000.00,
                'CDW PACK 1'          => 18000.00,
                'CDW PACK 2'          => 25000.00,
                'DECLINE PROTECTIONS' => 380000.00
            ],
            'E' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 8000.00,
                'CDW PACK 1'          => 20000.00,
                'CDW PACK 2'          => 30000.00,
                'DECLINE PROTECTIONS' => 500000.00
            ],
            'F' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 15000.00,
                'CDW PACK 1'          => 30000.00,
                'CDW PACK 2'          => 40000.00,
                'DECLINE PROTECTIONS' => 650000.00
            ],
            'IC' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 8000.00,
                'CDW PACK 1'          => 20000.00,
                'CDW PACK 2'          => 30000.00,
                'DECLINE PROTECTIONS' => 500000.00
            ],
            'I' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 10000.00,
                'CDW PACK 1'          => 30000.00,
                'CDW PACK 2'          => 40000.00,
                'DECLINE PROTECTIONS' => 600000.00
            ],
            'IB' => [
                'LDW PACK'            => 5000.00,
                'PDW PACK'            => 8000.00,
                'CDW PACK 1'          => 18000.00,
                'CDW PACK 2'          => 25000.00,
                'DECLINE PROTECTIONS' => 400000.00
            ],
            'M' => [
                'LDW PACK'            => 10000.00,
                'PDW PACK'            => 20000.00,
                'CDW PACK 1'          => 30000.00,
                'CDW PACK 2'          => 40000.00,
                'DECLINE PROTECTIONS' => 800000.00
            ],
            'L' => [
                'LDW PACK'            => 10000.00,
                'PDW PACK'            => 20000.00,
                'CDW PACK 1'          => 30000.00,
                'CDW PACK 2'          => 40000.00,
                'DECLINE PROTECTIONS' => 800000.00
            ],
            'H' => [
                'LDW PACK'            => 10000.00,
                'PDW PACK'            => 20000.00,
                'CDW PACK 1'          => 30000.00,
                'CDW PACK 2'          => 40000.00,
                'DECLINE PROTECTIONS' => 600000.00
            ],
            'HI' => [
                'LDW PACK'            => 10000.00,
                'PDW PACK'            => 20000.00,
                'CDW PACK 1'          => 30000.00,
                'CDW PACK 2'          => 40000.00,
                'DECLINE PROTECTIONS' => 900000.00
            ],
        ];

        foreach ($matrizGarantias as $codigoCategoria => $paquetes) {
            
            // Extrae el ID real buscando por el código (C, D, E...)
            $idCategoria = DB::table('categorias_carros')
                ->where('codigo', $codigoCategoria)
                ->value('id_categoria');

            if (!$idCategoria) {
                continue; // Si por alguna razón no encuentra la categoría, la salta
            }

            foreach ($paquetes as $nombrePaquete => $montoGarantia) {
                
                // Extrae el ID real buscando por el nombre del seguro
                $idPaquete = DB::table('seguro_paquete')
                    ->where('nombre', $nombrePaquete)
                    ->value('id_paquete');

                if ($idPaquete) {
                    DB::table('depositos')->insert([
                        'id_categoria' => $idCategoria,
                        'id_paquete'   => $idPaquete,
                        'monto'        => $montoGarantia,
                        'activo'       => 1,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }
}