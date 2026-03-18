<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioRolSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = DB::table('usuarios')->pluck('id_usuario', 'correo');
        $roles = DB::table('roles')->pluck('id_rol', 'nombre');

        $asignaciones = [
            [
                'id_usuario' => $usuarios['administrador@viajerocarental.com'],
                'id_rol' => $roles['SuperAdmin']
            ],
            [
                'id_usuario' => $usuarios['flotilla@viajerocarental.com'],
                'id_rol' => $roles['Flotilla']
            ],
            [
                'id_usuario' => $usuarios['reservaciones@viajerocarental.com'],
                'id_rol' => $roles['Ventas']
            ],
            [
                'id_usuario' => $usuarios['soportetecnico@viajerocarental.com'],
                'id_rol' => $roles['SuperAdmin']
            ],
        ];

        DB::table('usuario_rol')->insert($asignaciones);
    }
}
