<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaCostoKmSeeder extends Seeder
{
    public function run()
    {
        $costos = [
            'C'  => 9.00,
            'D'  => 10.00,
            'E'  => 11.00,
            'F'  => 12.00,
            'IC' => 11.00,
            'I'  => 11.00,
            'IB' => 11.00,
            'M'  => 13.00,
            'L'  => 15.00,
            'H'  => 15.00,
            'HI' => 15.00,
        ];

        foreach ($costos as $codigoCategoria => $costoKm) {

            $categoria = DB::table('categorias_carros')
                ->where('codigo', $codigoCategoria)
                ->first();

            if (!$categoria) continue;

            DB::table('categoria_costo_km')->insert([
                'id_categoria' => $categoria->id_categoria,
                'costo_km'     => $costoKm,
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
