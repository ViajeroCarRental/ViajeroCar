<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositoController extends Controller
{
    public function index()
    {
        try {
            $depositos = DB::table('depositos as d')
                ->join('categorias_carros as c', 'c.id_categoria', '=', 'd.id_categoria')
                ->join('seguro_paquete as p', 'p.id_paquete', '=', 'd.id_paquete')
                ->select(
                    'd.id_deposito', 
                    'd.monto', 
                    'c.nombre as categoria_nombre', 
                    'p.nombre as seguro_nombre',
                    'p.id_paquete'
                )
                ->orderBy('c.orden', 'asc')
                ->orderBy('p.orden', 'asc')
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            $depositos = DB::table('depositos as d')
                ->join('categorias_carros as c', 'c.id_categoria', '=', 'd.id_categoria')
                ->join('seguro_paquete as p', 'p.id_paquete', '=', 'd.id_paquete')
                ->select(
                    'd.id_deposito', 
                    'd.monto', 
                    'c.nombre as categoria_nombre', 
                    'p.nombre as seguro_nombre',
                    'p.id_paquete'
                )
                ->orderBy('c.id_categoria', 'asc')
                ->orderBy('p.id_paquete', 'asc')
                ->get();
        }

        // 🟢 Mandamos las listas para poder crear garantías nuevas manualmente
        $todasCategorias = DB::table('categorias_carros')->orderBy('orden', 'asc')->get();
        $todosPaquetes = DB::table('seguro_paquete')->orderBy('orden', 'asc')->get();

        return view('Admin.Deposito', compact('depositos', 'todasCategorias', 'todosPaquetes'));
    }

    // 🟢 NUEVO: Crear una garantía manualmente
    public function store(Request $request)
    {
        $request->validate([
            'id_categoria' => 'required',
            'id_paquete'   => 'required',
            'monto'        => 'required|numeric|min:0'
        ]);

        // Usamos updateOrInsert para que si ya existe la combinación, solo actualice el precio
        DB::table('depositos')->updateOrInsert(
            ['id_categoria' => $request->id_categoria, 'id_paquete' => $request->id_paquete],
            ['monto' => $request->monto, 'created_at' => now(), 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'Garantía agregada a la matriz correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0'
        ]);

        $actualizado = DB::table('depositos')
            ->where('id_deposito', $id)
            ->update([
                'monto'      => $request->monto,
                'updated_at' => now(),
            ]);

        if ($actualizado) {
            return redirect()->back()->with('success', 'Monto de garantía actualizado correctamente.');
        }

        return redirect()->back()->with('error', 'No se pudieron realizar cambios.');
    }

    // 🟢 NUEVO: Eliminar desde el modal
    public function destroy($id)
    {
        DB::table('depositos')->where('id_deposito', $id)->delete();
        return redirect()->back()->with('success', 'Garantía eliminada de la matriz.');
    }
}