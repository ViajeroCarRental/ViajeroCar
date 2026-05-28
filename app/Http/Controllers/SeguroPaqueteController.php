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
        
        // 🟢 AQUÍ ESTÁ LA MAGIA: Traemos los seguros individuales (protecciones) para los checkboxes
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
            ->pluck('monto', 'id_categoria'); 

        // 🟢 Buscamos qué protecciones ya tiene seleccionadas este paquete
        $proteccionesAsignadas = DB::table('paquete_seguro_individual')
            ->where('id_paquete', $id)
            ->pluck('id_individual');

        return response()->json([
            'ok' => true, 
            'data' => $paquete,
            'depositos' => $depositos,
            'protecciones' => $proteccionesAsignadas // Lo mandamos al JS
        ]);
    }

    // ===========================================
    // 4) Crear nuevo paquete
    // ===========================================
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_paquete = DB::table('seguro_paquete')->insertGetId([
                'nombre'             => $request->nombre,
                'descripcion'        => $request->descripcion,
                'precio_por_dia'     => $request->precio_por_dia,
                'deducible_colision' => $request->deducible_colision ?? 0.00,
                'deducible_robo'     => $request->deducible_robo ?? 0.00,
                'orden'              => $request->orden ?? 0,
                'activo'             => $request->activo ?? 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $deducibleTotal = ($request->deducible_colision ?? 0) + ($request->deducible_robo ?? 0);
            
            // Guardar porcentajes de los autos
            foreach ($request->porcentajes ?? [] as $id_categoria => $porcentaje) {
                $montoGarantia = $deducibleTotal * ($porcentaje / 100);
                DB::table('depositos')->updateOrInsert(
                    ['id_categoria' => $id_categoria, 'id_paquete' => $id_paquete],
                    ['monto' => $montoGarantia, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // 🟢 Guardar las protecciones (checkboxes) en la tabla intermedia
            foreach ($request->protecciones ?? [] as $id_individual) {
                DB::table('paquete_seguro_individual')->insert([
                    'id_paquete' => $id_paquete,
                    'id_individual' => $id_individual
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
        DB::beginTransaction();
        try {
            DB::table('seguro_paquete')
                ->where('id_paquete', $id)
                ->update([
                    'nombre'             => $request->nombre,
                    'descripcion'        => $request->descripcion,
                    'precio_por_dia'     => $request->precio_por_dia,
                    'deducible_colision' => $request->deducible_colision ?? 0.00,
                    'deducible_robo'     => $request->deducible_robo ?? 0.00,
                    'orden'              => $request->orden ?? 0,
                    'activo'             => $request->activo ?? 1,
                    'updated_at'         => now(),
                ]);

            $deducibleTotal = ($request->deducible_colision ?? 0) + ($request->deducible_robo ?? 0);
            
            foreach ($request->porcentajes ?? [] as $id_categoria => $porcentaje) {
                $montoGarantia = $deducibleTotal * ($porcentaje / 100);
                DB::table('depositos')->updateOrInsert(
                    ['id_categoria' => $id_categoria, 'id_paquete' => $id],
                    ['monto' => $montoGarantia, 'updated_at' => now()]
                );
            }

            // 🟢 Actualizar protecciones (borramos las viejas y guardamos las nuevas)
            DB::table('paquete_seguro_individual')->where('id_paquete', $id)->delete();
            foreach ($request->protecciones ?? [] as $id_individual) {
                DB::table('paquete_seguro_individual')->insert([
                    'id_paquete' => $id,
                    'id_individual' => $id_individual
                ]);
            }

            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Paquete actualizado']);

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