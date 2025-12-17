<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguroIndividualController extends Controller
{
    public function index()
    {
        return view('admin.paquetesindividuales');
    }

    public function list()
    {
        $data = DB::table('seguro_individuales')
            ->orderBy('id_individual', 'DESC')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function show($id)
    {
        $item = DB::table('seguro_individuales')->where('id_individual', $id)->first();

        return response()->json(['ok' => true, 'data' => $item]);
    }

    public function store(Request $request)
    {
        DB::table('seguro_individuales')->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_por_dia' => $request->precio_por_dia,
            'activo' => $request->activo ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, $id)
    {
        DB::table('seguro_individuales')
            ->where('id_individual', $id)
            ->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_por_dia' => $request->precio_por_dia,
                'activo' => $request->activo ?? 1,
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        DB::table('seguro_individuales')->where('id_individual', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
