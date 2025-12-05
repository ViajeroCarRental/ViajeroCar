<?php

namespace Database\Seeders;

// database/seeders/SucursalesSeeder.php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalesSeeder extends Seeder {
    public function run(): void {
        $map = DB::table('ciudades')->pluck('id_ciudad','nombre'); 

        $rows = [
            ['ciudad'=>'Querétaro','nombre'=>'Querétaro Aeropuerto'],
            ['ciudad'=>'Querétaro','nombre'=>'Querétaro Central de Autobuses'],
            ['ciudad'=>'Querétaro','nombre'=>'Querétaro Oficina Plaza Central Park'],

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
