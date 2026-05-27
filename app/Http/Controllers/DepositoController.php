<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositoController extends Controller
{
    public function index()
    {
        // 1. Paginamos las categorías (12 por página)
        $todasCategorias = DB::table('categorias_carros')
            ->orderBy('orden', 'asc')
            ->paginate(12);

        // 2. Obtenemos TODOS los paquetes (estos suelen ser pocos, no paginamos)
        $todosPaquetes = DB::table('seguro_paquete')->orderBy('orden', 'asc')->get();

        // 3. Obtenemos los depósitos existentes
        $depositosExistentes = DB::table('depositos')->get();

        // 4. Matriz de búsqueda rápida
        $matriz = [];
        foreach ($depositosExistentes as $d) {
            $matriz[$d->id_categoria][$d->id_paquete] = $d;
        }

        return view('Admin.Deposito', compact('matriz', 'todasCategorias', 'todosPaquetes'));
    }

    // Guardar o Actualizar (Individual o Masivo)
    public function store(Request $request)
    {
        // Caso Masivo (desde los nuevos modales de fila/columna)
        if ($request->has('bulk')) {
            $items = $request->input('items', []);
            foreach ($items as $item) {
                if (isset($item['monto']) && $item['monto'] !== null) {
                    DB::table('depositos')->updateOrInsert(
                        ['id_categoria' => $item['id_categoria'], 'id_paquete' => $item['id_paquete']],
                        ['monto' => $item['monto'], 'updated_at' => now()]
                    );
                }
            }
            return redirect()->back()->with('success', 'Matriz actualizada correctamente.');
        }

        // Caso Individual (Modal simple)
        $request->validate([
            'id_categoria' => 'required',
            'id_paquete'   => 'required',
            'monto'        => 'required|numeric|min:0'
        ]);

        DB::table('depositos')->updateOrInsert(
            ['id_categoria' => $request->id_categoria, 'id_paquete' => $request->id_paquete],
            ['monto' => $request->monto, 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'Garantía actualizada.');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['monto' => 'required|numeric|min:0']);

        DB::table('depositos')
            ->where('id_deposito', $id)
            ->update([
                'monto'      => $request->monto,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Monto actualizado.');
    }

    public function destroy($id)
    {
        DB::table('depositos')->where('id_deposito', $id)->delete();
        return redirect()->back()->with('success', 'Garantía eliminada.');
    }
}
