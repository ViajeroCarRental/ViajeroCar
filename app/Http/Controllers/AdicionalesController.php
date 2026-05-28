<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdicionalesController extends Controller
{
    public function index()
    {
        $servicios = DB::table('servicios')
            ->orderBy('id_servicio', 'asc')
            ->get();

        return view('Admin.Adicionales', compact('servicios'));
    }

    public function store(Request $request)
    {
        $request->merge([
        'nombre'      => mb_strtoupper(trim($request->nombre), 'UTF-8'),
        'descripcion' => $request->descripcion
            ? mb_strtoupper(trim($request->descripcion), 'UTF-8')
            : null,
        ]);

        $request->validate([
            'nombre'        => 'required|max:120|unique:servicios,nombre',
            'descripcion'   => 'nullable|max:255',
            'tipo_cobro'    => 'required|in:por_dia,por_evento,por_tanque',
            'precio'        => 'required|numeric|min:0',
            'activo'        => 'nullable|boolean',
            'usuario'       => 'nullable|boolean',
            'administrador' => 'nullable|boolean',
        ]);

        DB::table('servicios')->insert([
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'tipo_cobro'    => $request->tipo_cobro,
            'precio'        => $request->precio,
            'activo'        => $request->has('activo') ? 1 : 0,
            'usuario'       => $request->has('usuario') ? 1 : 0,
            'administrador' => $request->has('administrador') ? 1 : 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('servicios.index')->with('success', 'Servicio creado.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'        => 'required|max:120|unique:servicios,nombre,' . $id . ',id_servicio',
            'descripcion'   => 'nullable|max:255',
            'tipo_cobro'    => 'required|in:por_dia,por_evento,por_tanque',
            'precio'        => 'required|numeric|min:0',
            'activo'        => 'required|boolean',
            'usuario'       => 'required|boolean',
            'administrador' => 'required|boolean',
        ]);

        DB::table('servicios')
            ->where('id_servicio', $id)
            ->update([
                'nombre'        => $request->nombre,
                'descripcion'   => $request->descripcion,
                'tipo_cobro'    => $request->tipo_cobro,
                'precio'        => $request->precio,
                'activo'        => (int) $request->activo,
                'usuario'       => (int) $request->usuario,
                'administrador' => (int) $request->administrador,
                'updated_at'    => now(),
            ]);

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado.');
    }

    public function destroy($id)
    {
        DB::table('servicios')
            ->where('id_servicio', $id)
            ->delete();

        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado.');
    }
}
