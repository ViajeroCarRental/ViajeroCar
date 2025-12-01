<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicacionesServicioSeeder extends Seeder
{
    public function run()
    {
        // Lista de destinos con KM exactos
        $destinos = [
            ['estado' => 'Aguascalientes', 'destino' => 'Aeropuerto Internacional de Aguascalientes', 'km' => 310],
            ['estado' => 'CDMX', 'destino' => 'Aeropuerto Internacional de la Ciudad de México', 'km' => 230],
            ['estado' => 'CDMX', 'destino' => 'Aeropuerto Internacional Felipe Ángeles', 'km' => 200],
            ['estado' => 'Durango', 'destino' => 'Aeropuerto Internacional de Durango', 'km' => 700],
            ['estado' => 'Guanajuato', 'destino' => 'Aeropuerto Internacional de Guanajuato, Silao', 'km' => 160],
            ['estado' => 'Guanajuato', 'destino' => 'CELAYA', 'km' => 60],
            ['estado' => 'Guanajuato', 'destino' => 'Central de Autobuses León de los Aldamas', 'km' => 175],
            ['estado' => 'Guanajuato', 'destino' => 'SAN MIGUEL DE ALLENDE', 'km' => 60],
            ['estado' => 'Guanajuato', 'destino' => 'SAN JOSE ITURBIDE', 'km' => 60],
            ['estado' => 'Guerrero', 'destino' => 'Aeropuerto Internacional de Acapulco', 'km' => 590],
            ['estado' => 'Jalisco', 'destino' => 'Aeropuerto Internacional Miguel Hidalgo (GDL)', 'km' => 360],
            ['estado' => 'Jalisco', 'destino' => 'Aeropuerto Internacional Puerto Vallarta', 'km' => 700],
            ['estado' => 'Monterrey', 'destino' => 'Aeropuerto Internacional de Monterrey', 'km' => 700],
            ['estado' => 'Morelia', 'destino' => 'Aeropuerto Internacional General Francisco Mujica', 'km' => 215],
            ['estado' => 'Morelos', 'destino' => 'CUERNAVACA', 'km' => 350],
            ['estado' => 'Oaxaca', 'destino' => 'Aeropuerto Internacional de Oaxaca', 'km' => 700],
            ['estado' => 'Puebla', 'destino' => 'Aeropuerto Internacional de Puebla', 'km' => 336],
            ['estado' => 'Querétaro', 'destino' => 'AMELACO', 'km' => 55],
            ['estado' => 'Querétaro', 'destino' => 'BERNAL', 'km' => 60],
            ['estado' => 'Querétaro', 'destino' => 'SAN JOAQUIN', 'km' => 150],
            ['estado' => 'Querétaro', 'destino' => 'SAN JUAN DEL RIO QUERETARO', 'km' => 50],
            ['estado' => 'Querétaro', 'destino' => 'TEQUISQUIAPAN,QRO', 'km' => 60],
            ['estado' => 'Querétaro', 'destino' => 'QRO PARQUE INDUSTRIAL', 'km' => 50],
            ['estado' => 'San Luis Potosí', 'destino' => 'Aeropuerto Internacional de San Luis Potosí', 'km' => 230],
            ['estado' => 'Tabasco', 'destino' => 'Aeropuerto Internacional Carlos Rovirosa Pérez (VSA)', 'km' => 980],
            ['estado' => 'Tamaulipas', 'destino' => 'Aeropuerto Internacional de Tampico', 'km' => 700],
            ['estado' => 'Toluca', 'destino' => 'Aeropuerto Internacional de Toluca', 'km' => 270],
            ['estado' => 'Veracruz', 'destino' => 'Aeropuerto Internacional de Veracruz', 'km' => 610],
            ['estado' => 'Zacatecas', 'destino' => 'Aeropuerto Internacional de Zacatecas', 'km' => 500],
        ];

        foreach ($destinos as $d) {
            DB::table('ubicaciones_servicio')->insert([
                'estado'     => $d['estado'],
                'destino'    => $d['destino'],
                'km'         => $d['km'],
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
