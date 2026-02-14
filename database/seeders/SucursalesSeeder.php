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

            ['ciudad'=>'Aguascalientes','nombre'=>'Aeropuerto Internacional de Aguascalientes'],

            ['ciudad'=>'CDMX','nombre'=>'Aeropuerto Internacional de Ciudad de México'],
            ['ciudad'=>'CDMX','nombre'=>'Aeropuerto Internacional Felipe Ángeles'],

            ['ciudad'=>'Durango','nombre'=>'Aeropuerto Internacional de Durango'],

            ['ciudad'=>'Guanajuato','nombre'=>'Aeropuerto Internacional de Guanajuato, Silao'],
            ['ciudad'=>'Guanajuato','nombre'=>'Central de Autobuses León de los Aldamas'],

            ['ciudad'=>'Guerrero','nombre'=>'Aeropuerto Internacional de Acapulco'],

            ['ciudad'=>'Jalisco','nombre'=>'Aeropuerto Internacional Miguel Hidalgo (GDL)'],
            ['ciudad'=>'Jalisco','nombre'=>'Aeropuerto Internacional Puerto Vallarta'],

            ['ciudad'=>'Monterrey','nombre'=>'Aeropuerto Internacional de Monterrey'],

            ['ciudad'=>'Morelia','nombre'=>'Aeropuerto Internacional General Francisco Mujica'],

            ['ciudad'=>'Oaxaca','nombre'=>'Aeropuerto Internacional de Oaxaca'],

            ['ciudad'=>'Puebla','nombre'=>'Aeropuerto Internacional de Puebla'],

            ['ciudad'=>'San Luis Potosí','nombre'=>'Aeropuerto Internacional de San Luis Potosí'],

            ['ciudad'=>'Tabasco','nombre'=>'Aeropuerto Internacional Carlos Rovirosa Pérez (VSA)'],

            ['ciudad'=>'Tamaulipas','nombre'=>'Aeropuerto Internacional de Tampico'],

            ['ciudad'=>'Toluca','nombre'=>'Aeropuerto Internacional de Toluca'],

            ['ciudad'=>'Veracruz','nombre'=>'Aeropuerto Internacional de Veracruz'],

            ['ciudad'=>'Zacatecas','nombre'=>'Aeropuerto Internacional de Zacatecas'],

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
