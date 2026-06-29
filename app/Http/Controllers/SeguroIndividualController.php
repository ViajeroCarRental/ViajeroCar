<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguroIndividualController extends Controller
{
    // ===========================================
    // 1) Vista principal (Enviamos Secciones y Categorías)
    // ===========================================
    public function index()
    {
        $secciones = DB::table('secciones_seguros')->orderBy('nombre', 'asc')->get();
        $categorias = DB::table('categorias_carros')->orderBy('orden', 'asc')->get();

        return view('Admin.paquetesindividuales', compact('secciones', 'categorias'));
    }

    // ===========================================
    // 2) Listado para la tabla (AJAX)
    // ===========================================
    public function list()
    {
        $data = DB::table('seguro_individuales as si')
            ->join('secciones_seguros as ss', 'si.id_seccion', '=', 'ss.id_seccion')
            ->select('si.*', 'ss.nombre as seccion_nombre')
            ->orderBy('si.id_individual', 'DESC')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    // ===========================================
    // 3) Obtener un seguro por ID
    // ===========================================
    public function show($id)
    {
        $item = DB::table('seguro_individuales')->where('id_individual', $id)->first();

        if ($item && $item->precios_por_categoria) {
            $item->precios_por_categoria = json_decode($item->precios_por_categoria, true);
        }

        return response()->json(['ok' => true, 'data' => $item]);
    }

    // ===========================================
    // 4) Crear Seguro Individual
    // ===========================================
    public function store(Request $request)
    {
        DB::table('seguro_individuales')->insert([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_seccion'            => $request->id_seccion,
            'precio_por_dia'        => $request->precio_por_dia ?? 0,
            'precios_por_categoria' => json_encode($request->precios_por_categoria),
            'activo'                => $request->activo ?? 1,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ===========================================
    // 5) Actualizar Seguro Individual
    // ===========================================
    public function update(Request $request, $id)
    {
        DB::table('seguro_individuales')
            ->where('id_individual', $id)
            ->update([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
                'id_seccion'            => $request->id_seccion,
                'precio_por_dia'        => $request->precio_por_dia ?? 0,
                'precios_por_categoria' => json_encode($request->precios_por_categoria),
                'activo'                => $request->activo ?? 1,
                'updated_at'            => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    // ===========================================
    // 6) Eliminar Seguro
    // ===========================================
    public function destroy($id)
    {
        DB::table('seguro_individuales')->where('id_individual', $id)->delete();
        return response()->json(['ok' => true]);
    }

    // ===========================================
    // 7) Crear una nueva Sección al vuelo
    // ===========================================
    public function storeSeccion(Request $request)
    {
        try {
            $id = DB::table('secciones_seguros')->insertGetId([
                'nombre'      => $request->nombre,
                'requiere_desglose_autos' => $request->requiere_desglose ?? 0,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            $nuevaSeccion = DB::table('secciones_seguros')->where('id_seccion', $id)->first();

            return response()->json(['ok' => true, 'seccion' => $nuevaSeccion]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => 'Error al crear la sección. Es posible que el nombre ya exista.']);
        }
    }

    // ===========================================
    // 8) Listar todas las secciones (para la lista dentro del modal)
    // ===========================================
    public function listSecciones()
    {
        $data = DB::table('secciones_seguros')->orderBy('nombre', 'asc')->get();
        return response()->json(['ok' => true, 'data' => $data]);
    }

    // ===========================================
    // 9) Actualizar una Sección
    // ===========================================
    public function updateSeccion(Request $request, $id)
    {
        try {
            DB::table('secciones_seguros')
                ->where('id_seccion', $id)
                ->update([
                    'nombre'      => $request->nombre,
                    'requiere_desglose_autos' => $request->requiere_desglose ?? 0,
                    'updated_at'              => now(),
                ]);

            $seccion = DB::table('secciones_seguros')->where('id_seccion', $id)->first();
            return response()->json(['ok' => true, 'seccion' => $seccion]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => 'Error al actualizar la sección. Es posible que el nombre ya exista.']);
        }
    }

    // ===========================================
    // 10) Eliminar una Sección
    // ===========================================
    public function destroySeccion($id)
    {
        try {
            $enUso = DB::table('seguro_individuales')->where('id_seccion', $id)->count();
            if ($enUso > 0) {
                return response()->json(['ok' => false, 'msg' => 'No se puede eliminar: hay seguros usando esta sección.']);
            }

            DB::table('secciones_seguros')->where('id_seccion', $id)->delete();
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }
}