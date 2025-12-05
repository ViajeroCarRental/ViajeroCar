<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function index()
    {
        return view('admin.Roles');
    }

    public function listar()
{
    $roles = DB::table('roles')->get();

    $roles = $roles->map(function ($rol) {
        // obtener usuarios asignados
        $usuarios = DB::table('usuario_rol as ur')
            ->join('usuarios as u', 'ur.id_usuario', '=', 'u.id_usuario')
            ->where('ur.id_rol', $rol->id_rol)
            ->select('u.nombres', 'u.apellidos', 'u.correo')
            ->get();

        $rol->usuarios = $usuarios;
        return $rol;
    });

    return response()->json($roles);
}


    public function obtener($id)
    {
        $rol = DB::table('roles')->where('id_rol', $id)->first();

        // ⭐ tu tabla usuarios usa id_usuario
        $usuarios = DB::table('usuario_rol as ur')
            ->join('usuarios as u', 'ur.id_usuario', '=', 'u.id_usuario')
            ->where('ur.id_rol', $id)
            ->select('u.id_usuario', 'u.nombres', 'u.apellidos', 'u.correo')
            ->get();

        return response()->json([
            'rol' => $rol,
            'usuarios' => $usuarios
        ]);
    }

    public function crear(Request $r)
    {
        DB::table('roles')->insert([
            'nombre' => $r->nombre,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['ok' => true]);
    }

    public function actualizar(Request $r, $id)
    {
        DB::table('roles')->where('id_rol', $id)->update([
            'nombre' => $r->nombre,
            'updated_at' => now()
        ]);

        return response()->json(['ok' => true]);
    }

    public function eliminar($id)
    {
        DB::table('usuario_rol')->where('id_rol', $id)->delete();
        DB::table('roles')->where('id_rol', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
