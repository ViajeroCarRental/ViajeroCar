<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriasController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categorias_carros')
            ->orderBy('nombre', 'asc')
            ->get();

        $paquetes = DB::table('seguro_paquete')
            ->orderBy('nombre', 'asc')
            ->get();

        foreach ($categorias as $cat) {
            $cat->paquetes_asignados = isset($cat->paquetes) && $cat->paquetes ? json_decode($cat->paquetes, true) : [];
        }

        return view('Admin.Categorias', compact('categorias', 'paquetes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'            => 'required|max:10|unique:categorias_carros,codigo',
            'nombre'            => 'required|max:100',
            'precio_dia'        => 'required|numeric|min:0',
            'precio_semana'     => 'required|numeric|min:0',
            'precio_mes'        => 'required|numeric|min:0',
            'descuento_miembro' => 'required|numeric|min:0|max:100',
            'garantia_base'     => 'required|numeric|min:0',
            'activo'            => 'nullable|boolean',
            'paquetes'          => 'nullable|array',
        ]);

        DB::table('categorias_carros')->insert([
            'codigo'            => $request->codigo,
            'nombre'            => $request->nombre,
            'precio_dia'        => $request->precio_dia,
            'precio_semana'     => $request->precio_semana,
            'precio_mes'        => $request->precio_mes,
            'descuento_miembro' => $request->descuento_miembro,
            'garantia_base'     => $request->garantia_base,
            'activo'            => $request->has('activo') ? 1 : 0,
            'paquetes'          => $request->has('paquetes') ? json_encode($request->paquetes) : json_encode([]),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo'            => 'required|max:10|unique:categorias_carros,codigo,' . $id . ',id_categoria',
            'nombre'            => 'required|max:100',
            'precio_dia'        => 'required|numeric|min:0',
            'precio_semana'     => 'required|numeric|min:0',
            'precio_mes'        => 'required|numeric|min:0',
            'descuento_miembro' => 'required|numeric|min:0|max:100',
            'garantia_base'     => 'required|numeric|min:0',
            'activo'            => 'required|boolean',
            'paquetes'          => 'nullable|array',
        ]);

        DB::table('categorias_carros')
            ->where('id_categoria', $id)
            ->update([
                'codigo'            => $request->codigo,
                'nombre'            => $request->nombre,
                'precio_dia'        => $request->precio_dia,
                'precio_semana'     => $request->precio_semana,
                'precio_mes'        => $request->precio_mes,
                'descuento_miembro' => $request->descuento_miembro,
                'garantia_base'     => $request->garantia_base,
                'activo'            => (int) $request->activo,
                'paquetes'          => $request->has('paquetes') ? json_encode($request->paquetes) : json_encode([]),
                'updated_at'        => now(),
            ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy($id)
    {
        DB::table('categorias_carros')
            ->where('id_categoria', $id)
            ->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada.');
    }
}