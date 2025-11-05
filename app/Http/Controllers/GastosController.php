<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GastosController extends Controller
{
    // 🔹 Vista principal
    public function index()
    {
        $gastos = DB::table('gastos')
            ->join('vehiculos', 'gastos.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->select(
                'gastos.id_gasto',
                'gastos.fecha',
                'vehiculos.marca',
                'vehiculos.modelo',
                'vehiculos.placa',
                'gastos.tipo',
                'gastos.descripcion',
                'gastos.monto'
            )
            ->orderByDesc('gastos.fecha')
            ->get();

        return view('Admin.gastos', compact('gastos'));
    }

    // 🔹 Endpoint AJAX (filtrado por rango de fechas)
    public function filtrar(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $query = DB::table('gastos')
            ->join('vehiculos', 'gastos.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->select(
                'gastos.fecha',
                'vehiculos.marca',
                'vehiculos.modelo',
                'vehiculos.placa',
                'gastos.tipo',
                'gastos.descripcion',
                'gastos.monto'
            );

        if ($from && $to) {
            $query->whereBetween('gastos.fecha', [$from, $to]);
        }

        $data = $query->orderByDesc('gastos.fecha')->get();

        return response()->json($data);
    }
}
