<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservacionesActivasController extends Controller
{
    /**
     * 📋 Muestra todas las reservaciones activas (no canceladas ni expiradas)
     */
    public function index()
    {
        try {
            // 🔹 Consulta principal: reservaciones activas con su categoría
            $reservaciones = DB::table('reservaciones as r')
                ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
                ->select(
                    'r.id_reservacion',
                    'r.codigo',
                    'r.nombre_cliente',
                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.estado',
                    'r.metodo_pago',
                    'r.fecha_inicio',
                    'r.fecha_fin',
                    'r.total',
                    DB::raw('COALESCE(c.nombre, "Sin categoría") as categoria')
                )
                ->whereNotIn('r.estado', ['cancelada', 'expirada'])
                ->orderByDesc('r.id_reservacion')
                ->get();

            // 🔹 Renderiza la vista con los datos
            return view('Admin.ReservacionesActivas', compact('reservaciones'));

        } catch (\Throwable $e) {
            // 🔹 Mensaje amigable en caso de error
            return back()->with('error', 'Error al cargar las reservaciones activas: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Retorna los detalles completos de una reservación activa (por código)
     */
    public function show($codigo)
    {
        try {
            // 🔹 Buscar reservación por código con su categoría
            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
                ->select(
                    'r.id_reservacion',
                    'r.codigo',
                    'r.nombre_cliente',
                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.estado',
                    'r.fecha_inicio',
                    'r.hora_retiro',
                    'r.fecha_fin',
                    'r.hora_entrega',
                    'r.metodo_pago',
                    'r.total',
                    'r.tarifa_modificada',
                    DB::raw('COALESCE(c.nombre, "Sin categoría") as categoria')
                )
                ->where('r.codigo', $codigo)
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

    /**
 * 🗑️ Elimina una reservación activa
 */
public function destroy($id)
{
    try {
        // Verificar si existe
        $reserv = DB::table('reservaciones')->where('id_reservacion', $id)->first();

        if (!$reserv) {
            return back()->with('error', 'La reservación no existe.');
        }

        // Eliminar
        DB::table('reservaciones')->where('id_reservacion', $id)->delete();

        return back()->with('success', 'Reservación eliminada correctamente.');

    } catch (\Throwable $e) {
        return back()->with('error', 'Error al eliminar la reservación: ' . $e->getMessage());
    }
}

}
