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
                'nombres' => 'Alejandro',
                'apellidos' => 'Bernal Barrón',
                'correo' => 'superadmin@viajero.mx',
                'numero' => '4420000001',
                'contrasena_hash' => Hash::make('123456'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'Ricardo',
                'apellidos' => 'Flores Magón',
                'correo' => 'flotilla@viajero.mx',
                'numero' => '4420000002',
                'contrasena_hash' => Hash::make('123456'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'Nancy',
                'apellidos' => 'Cruz Pérez',
                'correo' => 'ventas@viajero.mx',
                'numero' => '4420000003',
                'contrasena_hash' => Hash::make('123456'),
                'email_verificado' => true,
                'pais' => 'México',
                'miembro_preferente' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Santos Ramírez',
                'correo' => 'usuario@viajero.mx',
                'numero' => '4420000004',
                'contrasena_hash' => Hash::make('123456'),
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
