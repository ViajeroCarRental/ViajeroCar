<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservacionesActivasController extends Controller
{
    /**
     * Muestra todas las reservaciones activas (no canceladas ni expiradas).
     */
    public function index()
    {
        try {
            // 🔹 Consulta principal: reservaciones activas
            $reservaciones = DB::table('reservaciones')
                ->select(
                    'id_reservacion',
                    'codigo',
                    'fecha_inicio',
                    'estado',
                    'total',
                    'nombre_cliente',
                    'email_cliente'
                )
                ->whereNotIn('estado', ['cancelada', 'expirada'])
                ->orderByDesc('id_reservacion')
                ->get();

            // 🔹 Formato uniforme para la vista
            $reservaciones->transform(function ($r) {
                $r->nombre_mostrar = $r->nombre_cliente ?? '—';
                $r->email_mostrar  = $r->email_cliente ?? '—';
                return $r;
            });

            // 🔹 Renderiza la vista con los datos
            return view('Admin.ReservacionesActivas', compact('reservaciones'));

        } catch (\Throwable $e) {
            // 🔹 Mensaje amigable en caso de error
            return back()->with('error', 'Error al cargar las reservaciones activas: ' . $e->getMessage());
        }
    }

    /**
 * Retorna los detalles completos de una reservación activa (por código).
 */
public function show($codigo)
{
    try {
        // 🔹 Busca la reservación por código y une datos del vehículo
        $reservacion = DB::table('reservaciones')
            ->join('vehiculos', 'reservaciones.id_vehiculo', '=', 'vehiculos.id_vehiculo')
            ->select(
                'reservaciones.codigo',
                'reservaciones.nombre_cliente',
                'reservaciones.email_cliente',
                'reservaciones.estado',
                'reservaciones.fecha_inicio',
                'reservaciones.hora_retiro',
                'reservaciones.fecha_fin',
                'reservaciones.hora_entrega',
                'reservaciones.metodo_pago',
                'reservaciones.total',
                DB::raw("CONCAT(vehiculos.marca, ' ', vehiculos.modelo) AS vehiculo")
            )
            ->where('reservaciones.codigo', $codigo)
            ->first();

        // 🔹 Validación: si no se encuentra
        if (!$reservacion) {
            return response()->json(['error' => 'Reservación no encontrada'], 404);
        }

        // 🔹 Respuesta exitosa en formato JSON
        return response()->json($reservacion, 200);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Error al obtener los detalles: ' . $e->getMessage()
        ], 500);
    }
}

}
