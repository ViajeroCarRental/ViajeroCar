<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriasController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categorias_carros')
            ->orderBy('id_categoria', 'asc')
            ->get();

        // ✅ tu vista real está en: resources/views/Admin/Categorias.blade.php
        return view('Admin.Categorias', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'     => 'required|max:10|unique:categorias_carros,codigo',
            'nombre'     => 'required|max:100',
            'precio_dia' => 'required|numeric|min:0',
            'activo'     => 'nullable|boolean',
        ]);

        DB::table('categorias_carros')->insert([
            'codigo'     => $request->codigo,
            'nombre'     => $request->nombre,
            'precio_dia' => $request->precio_dia,
            'activo'     => $request->has('activo') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✅ regresa a /admin/categorias (según tus rutas)
        return redirect()->route('categorias.index')->with('success', 'Categoría creada.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo'     => 'required|max:10|unique:categorias_carros,codigo,' . $id . ',id_categoria',
            'nombre'     => 'required|max:100',
            'precio_dia' => 'required|numeric|min:0',
            'activo'     => 'required|boolean',
        ]);

        DB::table('categorias_carros')
            ->where('id_categoria', $id)
            ->update([
                'codigo'     => $request->codigo,
                'nombre'     => $request->nombre,
                'precio_dia' => $request->precio_dia,
                'activo'     => (int) $request->activo,
                'updated_at' => now(),
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
