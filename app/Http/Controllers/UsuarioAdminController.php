<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class UsuarioAdminController extends Controller
{
    public function index(Request $request)
    {
        $totales = [
            'activos'      => DB::table('usuarios')->where('activo', 1)->count(),
            'desactivados' => DB::table('usuarios')->where('activo', 0)->count(),
            'admins'       => DB::table('usuario_rol')->distinct('id_usuario')->count('id_usuario'),
            'invitaciones' => DB::table('usuarios')->where('email_verificado', 0)->count(),
        ];

        $admins = DB::table('usuarios as u')
            ->join('usuario_rol as ur', 'u.id_usuario', '=', 'ur.id_usuario')
            ->join('roles as r', 'ur.id_rol', '=', 'r.id_rol')
            ->select(
                'u.id_usuario',
                'u.nombres',
                'u.apellidos',
                'u.correo',
                'u.numero',
                'u.activo',
                DB::raw('GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ", ") as roles'),
                DB::raw('MIN(ur.id_rol) as rol_id_principal')
            )
            ->groupBy('u.id_usuario','u.nombres','u.apellidos','u.correo','u.numero','u.activo')
            ->orderBy('u.nombres')
            ->get();

        $clientes = DB::table('usuarios as u')
            ->leftJoin('usuario_rol as ur', 'u.id_usuario', '=', 'ur.id_usuario')
            ->whereNull('ur.id_usuario')
            ->select(
                'u.id_usuario',
                'u.nombres',
                'u.apellidos',
                'u.correo',
                'u.numero',
                'u.pais',
                'u.activo',
                'u.miembro_preferente',
                'u.email_verificado'
            )
            ->orderBy('u.nombres')
            ->get();

        $roles = DB::table('roles')->orderBy('nombre')->get();

        return view('Admin.Usuarios', [
            'totales'  => $totales,
            'admins'   => $admins,
            'clientes' => $clientes,
            'roles'    => $roles,
        ]);
    }

    // ====================================================
    // 🟢 CREAR USUARIO ADMINISTRATIVO
    // ====================================================
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombres'   => 'required|string|max:120',
            'apellidos' => 'required|string|max:120',
            'correo'    => 'required|email|max:150|unique:usuarios,correo',
            'numero'    => 'nullable|string|max:20',
            'id_rol'    => 'required|exists:roles,id_rol',
            'activo'    => 'required|in:0,1',   // 🔥 Corregido
            'password'  => 'nullable|string'
        ]);

        // Si viene contraseña, usarla. Si no, default.
        $password = $data['password'] ?? '123456';

        $idUsuario = DB::table('usuarios')->insertGetId([
            'nombres'            => $data['nombres'],
            'apellidos'          => $data['apellidos'],
            'correo'             => $data['correo'],
            'numero'             => $data['numero'] ?? null,
            'contrasena_hash'    => Hash::make($password),
            'email_verificado'   => true,
            'pais'               => 'México',
            'miembro_preferente' => 0,
            'activo'             => $data['activo'],
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        DB::table('usuario_rol')->insert([
            'id_usuario' => $idUsuario,
            'id_rol'     => $data['id_rol'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Usuario administrativo creado correctamente.',
        ]);
    }

    // ====================================================
    // 🟡 ACTUALIZAR USUARIO ADMINISTRATIVO
    // ====================================================
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombres'   => 'required|string|max:120',
            'apellidos' => 'required|string|max:120',
            'correo'    => 'required|email|max:150|unique:usuarios,correo,' . $id . ',id_usuario',
            'numero'    => 'nullable|string|max:20',
            'id_rol'    => 'required|exists:roles,id_rol',
            'activo'    => 'required|in:0,1',   // 🔥 Corregido
            'password'  => 'nullable|string|min:6'
        ]);

        // Actualizar datos del usuario
        DB::table('usuarios')
            ->where('id_usuario', $id)
            ->update([
                'nombres'    => $data['nombres'],
                'apellidos'  => $data['apellidos'],
                'correo'     => $data['correo'],
                'numero'     => $data['numero'] ?? null,
                'activo'     => $data['activo'],
                'updated_at' => now(),
            ]);

        // Si incluyeron nueva contraseña
        if (!empty($data['password'])) {
            DB::table('usuarios')
                ->where('id_usuario', $id)
                ->update([
                    'contrasena_hash' => Hash::make($data['password']),
                ]);
        }

        // Reemplazar rol principal
        DB::table('usuario_rol')->where('id_usuario', $id)->delete();

        DB::table('usuario_rol')->insert([
            'id_usuario' => $id,
            'id_rol'     => $data['id_rol'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Usuario administrativo actualizado correctamente.',
        ]);
    }

    // ====================================================
    // 🔴 ELIMINAR USUARIO ADMINISTRATIVO
    // ====================================================
    public function destroy(int $id): JsonResponse
    {
        DB::table('usuario_rol')
            ->where('id_usuario', $id)
            ->delete();

        DB::table('usuarios')
            ->where('id_usuario', $id)
            ->update([
                'activo'     => 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Usuario administrativo eliminado (roles removidos).',
        ]);
    }
}
