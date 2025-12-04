<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GastosController extends Controller
{
    /**
     * 🔹 Vista principal
     */
    public function index()
    {
        // Obtener todos los gastos con datos del vehículo
        $gastos = DB::table('gastos')
            ->join('vehiculos', 'gastos.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->select(
                'gastos.id_gasto',
                'gastos.fecha',
                'vehiculos.nombre_publico',
                'vehiculos.placa',
                'gastos.tipo',
                'gastos.descripcion',
                'gastos.monto'
            )
            ->orderByDesc('gastos.fecha')
            ->get();

        return view('Admin.gastos', compact('gastos'));
    }

    /**
     * 🔹 Filtrar por rango de fechas (para AJAX)
     */
    public function filtrar(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $query = DB::table('gastos')
            ->join('vehiculos', 'gastos.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->select(
                'gastos.fecha',
                'vehiculos.nombre_publico',
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

    /**
     * 🔹 Totales acumulados por categoría
     * (para mostrar en los cuadros de la vista)
     */
    public function totales()
    {
        $totales = DB::table('gastos')
            ->select(
                DB::raw('SUM(monto) as total'),
                DB::raw('SUM(CASE WHEN LOWER(tipo) IN ("mantenimiento") THEN monto ELSE 0 END) as mantenimiento'),
                DB::raw('SUM(CASE WHEN LOWER(tipo) IN ("póliza", "poliza") THEN monto ELSE 0 END) as poliza'),
                DB::raw('SUM(CASE WHEN LOWER(tipo) IN ("carrocería", "carroceria") THEN monto ELSE 0 END) as carroceria'),
                DB::raw('SUM(CASE WHEN LOWER(tipo) NOT IN ("mantenimiento","póliza","poliza","carrocería","carroceria") THEN monto ELSE 0 END) as otros')
            )
            ->first();

        return response()->json($totales);
    }

    /**
     * 🔹 Exportar a Excel (CSV)
     */
    public function exportar()
    {
        $gastos = DB::table('gastos')
            ->join('vehiculos', 'gastos.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->select(
                'gastos.fecha',
                'vehiculos.nombre_publico as vehículo',
                'vehiculos.placa',
                'gastos.tipo',
                'gastos.descripcion',
                'gastos.monto'
            )
            ->orderBy('gastos.fecha', 'desc')
            ->get();

        // 🧾 Construir contenido CSV con BOM UTF-8
        $csvData = "\xEF\xBB\xBF"; // BOM para que Excel detecte UTF-8 correctamente
        $csvData .= "Fecha,Vehículo,Placa,Tipo,Descripción,Monto\n";

        foreach ($gastos as $g) {
            $fila = [
                $g->fecha,
                $g->vehículo,
                $g->placa,
                $g->tipo,
                str_replace(',', ';', $g->descripcion ?? ''),
                number_format((float)$g->monto, 2, '.', '') // ✅ mantiene los ceros decimales
            ];
            $csvData .= implode(',', $fila) . "\n";
        }

        $fileName = 'gastos_' . now()->format('Ymd_His') . '.csv';

        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    /**
     * 🔹 Rangos rápidos (Hoy, Semana, Mes)
     */
    public function rangoRapido($tipo)
    {
        $today = Carbon::today();

        switch ($tipo) {
            case 'hoy':
                $from = $today;
                $to = $today;
                break;
            case 'semana':
                $from = $today->copy()->startOfWeek();
                $to = $today->copy()->endOfWeek();
                break;
            case 'mes':
                $from = $today->copy()->startOfMonth();
                $to = $today->copy()->endOfMonth();
                break;
            default:
                return response()->json([]);
        }

        $data = DB::table('gastos')
            ->join('vehiculos', 'gastos.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->whereBetween('gastos.fecha', [$from, $to])
            ->select(
                'gastos.fecha',
                'vehiculos.nombre_publico',
                'vehiculos.placa',
                'gastos.tipo',
                'gastos.descripcion',
                'gastos.monto'
            )
            ->orderByDesc('gastos.fecha')
            ->get();

        return response()->json($data);
    }
}
