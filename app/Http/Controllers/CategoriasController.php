<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriasController extends Controller
{
    public function index()
    {
        // 🟢 Cambiado: Ahora ordena prioritariamente por tu columna de orden personalizado
        $categorias = DB::table('categorias_carros')
            ->orderBy('orden', 'asc')
            ->get();

        return view('Admin.Categorias', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'     => 'required|max:10|unique:categorias_carros,codigo',
            'nombre'     => 'required|max:100',
            'precio_dia' => 'required|numeric|min:0',
            'precio_semana'     => 'required|numeric|min:0',
            'precio_mes'        => 'required|numeric|min:0',
            'descuento_miembro' => 'required|numeric|min:0|max:100',
            'activo'     => 'nullable|boolean',
        ]);

        DB::table('categorias_carros')->insert([
            'codigo'     => $request->codigo,
            'nombre'     => $request->nombre,
            'precio_dia' => $request->precio_dia,
            'precio_semana'     => $request->precio_semana,
            'precio_mes'        => $request->precio_mes,
            'descuento_miembro' => $request->descuento_miembro,
            'activo'     => $request->has('activo') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
            'codigo'        => 'required|max:10|unique:categorias_carros,codigo',
            'nombre'        => 'required|max:100',
            'precio_dia'    => 'required|numeric|min:0',
            'garantia_base' => 'required|numeric|min:0', // 🟢 Validaciones agregadas
            'orden'         => 'required|integer|min:0',
            'activo'        => 'nullable|boolean',
        ]);

        DB::table('categorias_carros')->insert([
            'codigo'        => $request->codigo,
            'nombre'        => $request->nombre,
            'precio_dia'    => $request->precio_dia,
            'garantia_base' => $request->garantia_base, // 🟢 Inserta la garantía base
            'orden'         => $request->orden,         // 🟢 Inserta el orden
            'activo'        => $request->has('activo') ? 1 : 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo'     => 'required|max:10|unique:categorias_carros,codigo,' . $id . ',id_categoria',
            'nombre'     => 'required|max:100',
            'precio_dia' => 'required|numeric|min:0',
            'precio_semana'     => 'required|numeric|min:0',
            'precio_mes'        => 'required|numeric|min:0',
            'descuento_miembro' => 'required|numeric|min:0|max:100',
            'activo'     => 'required|boolean',
            'codigo'        => 'required|max:10|unique:categorias_carros,codigo,' . $id . ',id_categoria',
            'nombre'        => 'required|max:100',
            'precio_dia'    => 'required|numeric|min:0',
            'garantia_base' => 'required|numeric|min:0', // 🟢 Validaciones agregadas
            'orden'         => 'required|integer|min:0',
            'activo'        => 'required|boolean',
        ]);

        DB::table('categorias_carros')
            ->where('id_categoria', $id)
            ->update([
                'codigo'     => $request->codigo,
                'nombre'     => $request->nombre,
                'precio_dia' => $request->precio_dia,
                'precio_semana'     => $request->precio_semana,
                'precio_mes'        => $request->precio_mes,
                'descuento_miembro' => $request->descuento_miembro,
                'activo'     => (int) $request->activo,
                'updated_at' => now(),
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'precio_dia'    => $request->precio_dia,
                'garantia_base' => $request->garantia_base, // 🟢 Actualiza la garantía base
                'orden'         => $request->orden,         // 🟢 Actualiza el orden
                'activo'        => (int) $request->activo,
                'updated_at'    => now(),
            ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada con éxito.');
    }

    public function destroy($id)
    {
        DB::table('categorias_carros')
            ->where('id_categoria', $id)
            ->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada.');
    }
}
