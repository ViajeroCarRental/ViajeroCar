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
            'codigo'        => 'required|max:10|unique:categorias_carros,codigo',
            'nombre'        => 'required|max:100',
            'descripcion'   => 'required|string|max:255',
            'precio_dia'    => 'required|numeric|gt:0',
            'precio_semana' => 'required|numeric|gt:0',
            'precio_mes'    => 'required|numeric|gt:0',
            'garantia_base' => 'required|numeric|gt:0',
            'activo'        => 'nullable|boolean',
            'paquetes'      => 'nullable|array',
        ], [
            'codigo.unique'        => 'El código que intentas asignar ya le pertenece a otra categoría.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'precio_dia.gt'        => 'El precio por día debe ser mayor a 0.',
            'precio_dia.required'  => 'El precio por día es obligatorio.',
            'precio_semana.gt'     => 'El precio por semana debe ser mayor a 0.',
            'precio_mes.gt'        => 'El precio por mes debe ser mayor a 0.',
            'garantia_base.gt'     => 'La garantía base debe ser mayor a 0.',
        ]);

        DB::table('categorias_carros')->insert([
            'codigo'        => mb_strtoupper($request->codigo, 'UTF-8'),
            'nombre'        => mb_strtoupper($request->nombre, 'UTF-8'),
            'descripcion'   => $request->descripcion,
            'precio_dia'    => $request->precio_dia,
            'precio_semana' => $request->precio_semana,
            'precio_mes'    => $request->precio_mes,
            'garantia_base' => $request->garantia_base,
            'activo'        => $request->has('activo') ? 1 : 0,
            'paquetes'      => $request->has('paquetes') ? json_encode($request->paquetes) : json_encode([]),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo'        => 'required|max:10|unique:categorias_carros,codigo,' . $id . ',id_categoria',
            'nombre'        => 'required|max:100',
            'descripcion'   => 'required|string|max:255',
            'precio_dia'    => 'required|numeric|gt:0',
            'precio_semana' => 'required|numeric|gt:0',
            'precio_mes'    => 'required|numeric|gt:0',
            'garantia_base' => 'required|numeric|gt:0',
            'activo'        => 'required|boolean',
            'paquetes'      => 'nullable|array',
        ], [
            'codigo.unique'        => 'El código que intentas asignar ya le pertenece a otra categoría.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'precio_dia.gt'        => 'El precio por día debe ser mayor a 0.',
            'precio_semana.gt'     => 'El precio por semana debe ser mayor a 0.',
            'precio_mes.gt'        => 'El precio por mes debe ser mayor a 0.',
            'garantia_base.gt'     => 'La garantía base debe ser mayor a 0.',
        ]);

        DB::table('categorias_carros')
            ->where('id_categoria', $id)
            ->update([
                'codigo'        => mb_strtoupper($request->codigo, 'UTF-8'),
                'nombre'        => mb_strtoupper($request->nombre, 'UTF-8'),
                'descripcion'   => $request->descripcion,
                'precio_dia'    => $request->precio_dia,
                'precio_semana' => $request->precio_semana,
                'precio_mes'    => $request->precio_mes,
                'garantia_base' => $request->garantia_base,
                'activo'        => (int) $request->activo,
                'paquetes'      => $request->has('paquetes') ? json_encode($request->paquetes) : json_encode([]),
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
