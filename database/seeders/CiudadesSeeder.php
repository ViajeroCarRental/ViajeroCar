<?php

namespace Database\Seeders;

// database/seeders/CiudadesSeeder.php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiudadesSeeder extends Seeder {
    public function run(): void {
        $qs = [
            ['nombre'=>'Querétaro','estado'=>'Querétaro','pais'=>'México'],
        ];
        foreach($qs as $c){
            DB::table('ciudades')->updateOrInsert(
                ['nombre'=>$c['nombre'],'estado'=>$c['estado'],'pais'=>$c['pais']],
                ['created_at'=>now(),'updated_at'=>now()]
            );
        }
    }
}

