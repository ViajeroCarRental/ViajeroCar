<?php

namespace Database\Seeders;

// database/seeders/CiudadesSeeder.php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiudadesSeeder extends Seeder {
    public function run(): void {
        $qs = [
            ['nombre'=>'Querétaro','estado'=>'Querétaro','pais'=>'México'],
            ['nombre'=>'Aguascalientes','estado'=>'Aguascalientes','pais'=>'México'],
            ['nombre'=>'CDMX','estado'=>'CDMX','pais'=>'México'],
            ['nombre'=>'Durango','estado'=>'Durango','pais'=>'México'],
            ['nombre'=>'Guanajuato','estado'=>'Guanajuato','pais'=>'México'],
            //['nombre'=>'Guanajuato','estado'=>'León','pais'=>'México'],
            ['nombre'=>'Guerrero','estado'=>'Acapulco','pais'=>'México'],
            ['nombre'=>'Jalisco','estado'=>'Jalisco','pais'=>'México'],
            //['nombre'=>'Jalisco','estado'=>'Puerto Vallarta','pais'=>'México'],
            ['nombre'=>'Monterrey','estado'=>'Nuevo León','pais'=>'México'],
            ['nombre'=>'Morelia','estado'=>'Michoacán','pais'=>'México'],
            ['nombre'=>'Oaxaca','estado'=>'Oaxaca','pais'=>'México'],
            ['nombre'=>'Puebla','estado'=>'Puebla','pais'=>'México'],
            ['nombre'=>'San Luis Potosí','estado'=>'San Luis Potosí','pais'=>'México'],
            ['nombre'=>'Tabasco','estado'=>'Villahermosa','pais'=>'México'],
            ['nombre'=>'Tamaulipas','estado'=>'Tampico','pais'=>'México'],
            ['nombre'=>'Toluca','estado'=>'Toluca','pais'=>'México'],
            ['nombre'=>'Veracruz','estado'=>'Veracruz','pais'=>'México'],
            ['nombre'=>'Zacatecas','estado'=>'Zacatecas','pais'=>'México'],

        ];
        foreach($qs as $c){
            DB::table('ciudades')->updateOrInsert(
                ['nombre'=>$c['nombre'],'estado'=>$c['estado'],'pais'=>$c['pais']],
                ['created_at'=>now(),'updated_at'=>now()]
            );
        }
    }
}

