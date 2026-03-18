<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {


        $usuarios = [
            [
                'nombres' => 'Jose Juan',
                'apellidos' => 'De Dios Resendiz',
                'correo' => 'administrador@viajerocarental.com',
                'numero' => '4427169793',
                'contrasena_hash' => Hash::make('admin2025'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'Iván ',
                'apellidos' => 'Ruiz',
                'correo' => 'reservaciones@viajerocarental.com',
                'numero' => '4461700006',
                'contrasena_hash' => Hash::make('ventas2025'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'Paola',
                'apellidos' => 'Herdandez',
                'correo' => 'soportetecnico@viajerocarental.com',
                'numero' => '4425574508',
                'contrasena_hash' => Hash::make('soporte2025'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'Toño',
                'apellidos' => 'García',
                'correo' => 'flotilla@viajerocarental.com',
                'numero' => '4420000004',
                'contrasena_hash' => Hash::make('flotilla2025'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('usuarios')->insert($usuarios);
    }
}
