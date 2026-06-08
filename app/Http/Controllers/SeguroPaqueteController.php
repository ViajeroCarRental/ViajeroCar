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
        $categorias = DB::table('categorias_carros')->orderBy('orden', 'asc')->get();
        $protecciones = DB::table('seguro_individuales')->where('activo', 1)->orderBy('nombre', 'asc')->get();

        return view('Admin.paqueteseguros', compact('categorias', 'protecciones'));
    }

    // ===========================================
    // 2) Listado (AJAX)
    // ===========================================
    public function list()
    {
        $data = DB::table('seguro_paquete')->orderBy('orden', 'asc')->get();
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

        $depositos = DB::table('depositos')
            ->where('id_paquete', $id)
            ->get()
            ->pluck('monto', 'id_categoria')
            ->toArray();

        $proteccionesAsignadas = DB::table('paquete_seguro_individual')
            ->where('id_paquete', $id)
            ->pluck('id_individual');

        return response()->json([
            'ok' => true,
            'data' => $paquete,
            'depositos' => (object)$depositos, 
            'protecciones' => $proteccionesAsignadas
        ]);
    }

    // ===========================================
    // 4) Crear nuevo paquete
    // ===========================================
    public function store(Request $request)
    {
        $nombre = $request->nombre;

        $existe = DB::table('seguro_paquete')->where('nombre', $nombre)->exists();
        if ($existe) {
            return response()->json(['ok' => false, 'msg' => 'Ya existe un paquete registrado con el nombre "' . $nombre . '".']);
        }

        DB::beginTransaction();
        try {
            $id_paquete = DB::table('seguro_paquete')->insertGetId([
                'nombre'             => $nombre,
                'descripcion'        => $request->descripcion,
                'precio_por_dia'     => $request->precio_por_dia,
                'deducible_colision' => $request->deducible_colision ?? 0.00,
                'deducible_robo'     => $request->deducible_robo ?? 0.00,
                'orden'              => $request->orden ?? 0,
                'activo'             => $request->activo ?? 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            foreach ($request->montos ?? [] as $id_categoria => $monto) {
                $id_cat_int = (int)$id_categoria; 
                
                DB::table('depositos')->insert([
                    'id_categoria' => $id_cat_int, 
                    'id_paquete' => $id_paquete,
                    'monto' => (float)$monto,
                    'created_at' => now(), 
                    'updated_at' => now()
                ]);
            }

            foreach ($request->protecciones ?? [] as $id_individual) {
                DB::table('paquete_seguro_individual')->insert([
                    'id_paquete' => $id_paquete,
                    'id_individual' => (int)$id_individual
                ]);
            }

            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Paquete guardado con éxito']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    // ===========================================
    // 5) Actualizar paquete
    // ===========================================
    public function update(Request $request, $id)
    {
        $nombre = $request->nombre;

        $existe = DB::table('seguro_paquete')
            ->where('nombre', $nombre)
            ->where('id_paquete', '!=', $id)
            ->exists();

        if ($existe) {
            return response()->json(['ok' => false, 'msg' => 'No se puede actualizar. El nombre "' . $nombre . '" ya pertenece a otro paquete.']);
        }

        DB::beginTransaction();
        try {
            DB::table('seguro_paquete')
                ->where('id_paquete', $id)
                ->update([
                    'nombre'             => $nombre,
                    'descripcion'        => $request->descripcion,
                    'precio_por_dia'     => $request->precio_por_dia,
                    'deducible_colision' => $request->deducible_colision ?? 0.00,
                    'deducible_robo'     => $request->deducible_robo ?? 0.00,
                    'orden'              => $request->orden ?? 0,
                    'activo'             => $request->activo ?? 1,
                    'updated_at'         => now(),
                ]);

            // ACTUALIZAR MONTOS DIRECTOS
            foreach ($request->montos ?? [] as $id_categoria => $monto) {
                $id_cat_int = (int)$id_categoria; 

                DB::table('depositos')->updateOrInsert(
                    ['id_categoria' => $id_cat_int, 'id_paquete' => $id],
                    ['monto' => (float)$monto, 'updated_at' => now()]
                );
            }

            DB::table('paquete_seguro_individual')->where('id_paquete', $id)->delete();
            foreach ($request->protecciones ?? [] as $id_individual) {
                DB::table('paquete_seguro_individual')->insert([
                    'id_paquete' => $id,
                    'id_individual' => (int)$id_individual
                ]);
            }

            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Paquete actualizado con éxito']);
        } catch (\Exception $e) {
            DB::rollBack();
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
            return response()->json(['ok' => true, 'msg' => 'Paquete eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }
}