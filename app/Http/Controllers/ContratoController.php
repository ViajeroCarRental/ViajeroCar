<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContratoController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 🔍 Detectar si viene id_reservacion o código
            $id_reservacion = $request->query('id_reservacion');
            $codigo = $request->query('codigo');

            // 🧩 Buscar reservación
            if ($id_reservacion) {
                $reservacion = DB::table('reservaciones')
                    ->where('id_reservacion', $id_reservacion)
                    ->first();
            } elseif ($codigo) {
                $reservacion = DB::table('reservaciones')
                    ->where('codigo', $codigo)
                    ->first();
            } else {
                $reservacion = null;
            }

            // 🚗 Obtener vehículo asociado
            $vehiculo = $reservacion
                ? DB::table('vehiculos')->where('id_vehiculo', $reservacion->id_vehiculo)->first()
                : null;

            // 🛡️ Cargar protecciones disponibles
            $seguros = DB::table('seguro_paquete')
                ->select('id_paquete', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
                ->where('activo', true)
                ->orderBy('precio_por_dia')
                ->get();

            // ✅ Renderizar vista con datos
            return view('admin.Contrato', compact('reservacion', 'vehiculo', 'seguros'));
        } catch (\Throwable $e) {
            Log::error("❌ Error al cargar contrato: " . $e->getMessage());
            return back()->withErrors(['msg' => 'Error al cargar la vista del contrato']);
        }
    }




    /**
     * Obtener servicios adicionales (Paso 2)
     */
    public function getServicios()
    {
        try {
            $servicios = DB::table('servicios_adicionales')
                ->select('id_servicio', 'nombre', 'descripcion', 'precio', 'tipo', 'activo')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            return response()->json($servicios);

        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener servicios: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener seguros / protecciones (Paso 3)
     */
    public function getSeguros()
    {
        try {
            $seguros = DB::table('seguro_paquete')
                ->select('id_paquete', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
                ->where('activo', true)
                ->orderBy('precio_por_dia')
                ->get();

            return response()->json($seguros);

        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener seguros: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
