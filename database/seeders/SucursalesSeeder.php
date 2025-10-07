<?php

namespace Database\Seeders;

// database/seeders/SucursalesSeeder.php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalesSeeder extends Seeder {
    public function run(): void {
        $map = DB::table('ciudades')->pluck('id_ciudad','nombre'); // ['Querétaro'=>1, 'León'=>2, ...]

        $rows = [
            ['ciudad'=>'Querétaro','nombre'=>'Querétaro Aeropuerto'],
            ['ciudad'=>'Querétaro','nombre'=>'Querétaro Central de Autobuses'],
            ['ciudad'=>'León','nombre'=>'León Aeropuerto'],
            ['ciudad'=>'León','nombre'=>'León Central de Autobuses'],
            ['ciudad'=>'Guanajuato','nombre'=>'Guanajuato Centro'],
        ];

        foreach($rows as $r){
            $idCiudad = $map[$r['ciudad']] ?? null;
            if(!$idCiudad) continue;

            DB::table('sucursales')->updateOrInsert(
                ['id_ciudad'=>$idCiudad, 'nombre'=>$r['nombre']],
                ['activo'=>true, 'created_at'=>now(), 'updated_at'=>now()]
            );
        }
    }
}
