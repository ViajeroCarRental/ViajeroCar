<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    /**
     * Vista principal
     */
    public function index()
    {
        return view('admin.Roles');
    }

    /**
     * Listar roles
     */
    public function list()
    {
        $roles = DB::table('roles as r')
            ->leftJoin('usuario_rol as ur', 'r.id_rol', '=', 'ur.id_rol')
            ->select(
                'r.id_rol',
                'r.nombre',
                DB::raw('COUNT(ur.id_usuario) as total_usuarios')
            )
            ->groupBy('r.id_rol', 'r.nombre')
            ->get();

        return response()->json($roles);
    }

    /**
     * Obtener rol + permisos + usuarios
     */
    public function show($id)
    {
        $rol = DB::table('roles')->where('id_rol', $id)->first();

        $usuarios = DB::table('usuario_rol as ur')
            ->join('usuarios as u', 'ur.id_usuario', '=', 'u.id_usuario')
            ->where('ur.id_rol', $id)
            ->select('u.id_usuario', 'u.nombres', 'u.apellidos', 'u.correo')
            ->get();

        // obtener todos los permisos
        $permisos = DB::table('permisos')->get();

        // permisos asignados
        $permisosAsignados = DB::table('rol_permiso')
            ->where('id_rol', $id)
            ->pluck('id_permiso')
            ->toArray();

        return response()->json([
            'rol' => $rol,
            'usuarios' => $usuarios,
            'permisos' => $permisos,
            'permisosAsignados' => $permisosAsignados
        ]);
    }

    /**
     * Crear rol
     */
    public function store(Request $r)
    {
        $id = DB::table('roles')->insertGetId([
            'nombre' => $r->nombre,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($r->permisos) {
            foreach ($r->permisos as $p) {
                DB::table('rol_permiso')->insert([
                    'id_rol' => $id,
                    'id_permiso' => $p
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Actualizar rol
     */
    public function update(Request $r, $id)
    {
        DB::table('roles')->where('id_rol', $id)->update([
            'nombre' => $r->nombre,
            'updated_at' => now()
        ]);

        DB::table('rol_permiso')->where('id_rol', $id)->delete();

        if ($r->permisos) {
            foreach ($r->permisos as $p) {
                DB::table('rol_permiso')->insert([
                    'id_rol' => $id,
                    'id_permiso' => $p
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Eliminar rol
     */
    public function destroy($id)
    {
        DB::table('rol_permiso')->where('id_rol', $id)->delete();
        DB::table('usuario_rol')->where('id_rol', $id)->delete();
        DB::table('roles')->where('id_rol', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
