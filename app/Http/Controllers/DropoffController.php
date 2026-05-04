<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DropoffController extends Controller
{
    public function index()
    {
        return view('Admin.dropoff');
    }

    public function data()
    {
        $categorias = DB::table('categorias_carros as c')
            ->leftJoin('categoria_costo_km as ck', 'c.id_categoria', '=', 'ck.id_categoria')
            ->select(
                'c.id_categoria',
                'c.codigo',
                'c.nombre',
                'c.activo',
                DB::raw('IFNULL(ck.costo_km, 0) as costo_km')
            )
            ->get();

        $ubicaciones = DB::table('ubicaciones_servicio')->get();

        return response()->json([
            'categorias' => $categorias,
            'ubicaciones' => $ubicaciones
        ]);
    }

    public function updateKm(Request $request)
    {
        DB::table('ubicaciones_servicio')
            ->where('id_ubicacion', $request->id)
            ->update([
                'km' => $request->km,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function updateCostoKm(Request $request)
    {
        DB::table('categoria_costo_km')->updateOrInsert(
            ['id_categoria' => $request->id_categoria],
            [
                'costo_km' => $request->costo_km,
                'updated_at' => now()
            ]
        );

        return response()->json(['success' => true]);
    }
}
