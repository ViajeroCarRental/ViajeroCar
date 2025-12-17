<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguroPaqueteController extends Controller
{
    // ===========================================
    // 1) Vista principal
    // ===========================================
    public function index()
    {
        return view('Admin.paqueteseguros');

    }

    // ===========================================
    // 2) Listado (AJAX)
    // ===========================================
    public function list()
    {
        $data = DB::table('seguro_paquete')
            ->orderBy('id_paquete', 'DESC')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    // ===========================================
    // 3) Obtener un paquete por ID
    // ===========================================
    public function show($id)
    {
        $paquete = DB::table('seguro_paquete')->where('id_paquete', $id)->first();

        if (!$paquete) {
            return response()->json(['ok' => false, 'msg' => 'Paquete no encontrado']);
        }

        return response()->json(['ok' => true, 'data' => $paquete]);
    }

    // ===========================================
    // 4) Crear nuevo paquete
    // ===========================================
    public function store(Request $request)
    {
        try {
            DB::table('seguro_paquete')->insert([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_por_dia' => $request->precio_por_dia,
                'activo' => $request->activo ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['ok' => true, 'msg' => 'Paquete creado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    // ===========================================
    // 5) Actualizar paquete
    // ===========================================
    public function update(Request $request, $id)
    {
        try {
            DB::table('seguro_paquete')
                ->where('id_paquete', $id)
                ->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'precio_por_dia' => $request->precio_por_dia,
                    'activo' => $request->activo ?? 1,
                    'updated_at' => now(),
                ]);

            return response()->json(['ok' => true, 'msg' => 'Paquete actualizado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    // ===========================================
    // 6) Eliminar paquete
    // ===========================================
    public function destroy($id)
    {
        try {
            DB::table('seguro_paquete')->where('id_paquete', $id)->delete();

            return response()->json(['ok' => true, 'msg' => 'Paquete eliminado']);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }
}
