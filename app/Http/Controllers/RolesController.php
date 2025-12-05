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
        $roles = DB::table('roles as r')
            ->leftJoin('usuario_rol as ur', 'r.id_rol', '=', 'ur.id_rol')
            ->select('r.id_rol', 'r.nombre', DB::raw('COUNT(ur.id_usuario) as total_usuarios'))
            ->groupBy('r.id_rol', 'r.nombre')
            ->get();

        return response()->json($roles);
    }

    public function obtener($id)
    {
        $rol = DB::table('roles')->where('id_rol', $id)->first();

        $usuarios = DB::table('usuario_rol as ur')
            ->join('usuarios as u', 'ur.id_usuario', '=', 'u.id_usuario')
            ->where('ur.id_rol', $id)
            ->select('u.id_usuario', 'u.nombres', 'u.apellidos', 'u.correo')
            ->get();

        $permisosAsignados = DB::table('rol_permiso')
            ->where('id_rol', $id)
            ->pluck('id_permiso')
            ->toArray();

        return response()->json([
            'rol' => $rol,
            'usuarios' => $usuarios,
            'permisosAsignados' => $permisosAsignados
        ]);
    }

    public function crear(Request $r)
    {
        $permisos = json_decode($r->permisos, true);

        $id = DB::table('roles')->insertGetId([
            'nombre' => $r->nombre,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($permisos) {
            foreach ($permisos as $permiso) {
                DB::table('rol_permiso')->insert([
                    'id_rol' => $id,
                    'id_permiso' => $permiso
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function actualizar(Request $r, $id)
    {
        $permisos = json_decode($r->permisos, true);

        DB::table('roles')->where('id_rol', $id)->update([
            'nombre' => $r->nombre,
            'updated_at' => now()
        ]);

        DB::table('rol_permiso')->where('id_rol', $id)->delete();

        if ($permisos) {
            foreach ($permisos as $permiso) {
                DB::table('rol_permiso')->insert([
                    'id_rol' => $id,
                    'id_permiso' => $permiso
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function eliminar($id)
    {
        DB::table('rol_permiso')->where('id_rol', $id)->delete();
        DB::table('usuario_rol')->where('id_rol', $id)->delete();
        DB::table('roles')->where('id_rol', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
