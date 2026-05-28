<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlotillaStatusController extends Controller
{
    public function index()
    {
         $estatusList = DB::table('estatus_carro')
        ->whereIn('nombre', ['disponible', 'Mantenimiento', 'Rentado', 'Baja'])
        ->orderBy('id_estatus')
        ->get();

    return view('Admin.FlotillaStatus', compact('estatusList'));
    }

    public function list()
    {
        $vehiculos = DB::table('vehiculos as v')
            ->leftJoin('categorias_carros as c', 'v.id_categoria', '=', 'c.id_categoria')
            ->leftJoin('mantenimientos as m', 'v.id_vehiculo', '=', 'm.id_vehiculo')
            ->leftJoin('estatus_carro as e', 'v.id_estatus', '=', 'e.id_estatus')
            ->select(
                'v.id_vehiculo',
                'v.placa',
                'c.codigo as categoria',
                'c.nombre as tamano',
                'v.modelo',
                'v.transmision',
                'v.color',
                'v.gasolina_actual',
                'v.capacidad_tanque',
                DB::raw("
                    CASE
                        WHEN v.capacidad_tanque > 0
                        THEN ROUND((v.gasolina_actual / v.capacidad_tanque) * 16)
                        ELSE 0
                    END as gasolina_fraccion
                "),
                'v.kilometraje',
                'v.vigencia_verificacion',
                'm.intervalo_km',
                'v.fin_vigencia_poliza',
                'e.nombre as estatus'
            )
            ->orderBy('v.placa')
            ->get();

        return response()->json($vehiculos);
    }

    public function updateEstatus(Request $request, $id)
{
    $request->validate([
        'id_estatus' => 'required|integer|exists:estatus_carro,id_estatus',
    ]);

    // Solo permitir los 4 estatus válidos (defensa extra)
    $estatusValido = DB::table('estatus_carro')
        ->where('id_estatus', $request->id_estatus)
        ->whereIn('nombre', ['disponible', 'Mantenimiento', 'Rentado', 'Baja'])
        ->exists();

    if (!$estatusValido) {
        return response()->json([
            'ok' => false,
            'message' => 'Estatus no permitido.'
        ], 422);
    }

    DB::table('vehiculos')
        ->where('id_vehiculo', $id)
        ->update([
            'id_estatus' => $request->id_estatus,
            'updated_at' => now(),
        ]);

    return response()->json(['ok' => true]);
}
}
