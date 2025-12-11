<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservacionesActivasController extends Controller
{
    /**
     * 📋 Muestra todas las reservaciones activas (no canceladas ni expiradas)
     */
    public function index(Request $request)
    {
        try {
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
                    'r.hora_retiro',
                    'r.fecha_fin',
                    'r.total',
                    'r.sucursal_retiro',
                    'r.no_vuelo',              // 👈 NUEVO: número de vuelo
                    'c.codigo as categoria'    // C, D, E, H, etc.
                )
                // solo activas
                ->whereNotIn('r.estado', ['cancelada', 'expirada'])

                // 🔽 Filtro opcional por sucursal_retiro (Aeropuerto / Central / Central Park)
                ->when($request->filled('sucursal'), function ($q) use ($request) {
                    $q->where('r.sucursal_retiro', $request->sucursal);
                })

                // 🔎 (Opcional) Filtro por texto q en nombre o correo
                ->when($request->filled('q'), function ($qBuilder) use ($request) {
                    $term = '%' . $request->q . '%';
                    $qBuilder->where(function ($sub) use ($term) {
                        $sub->where('r.nombre_cliente', 'LIKE', $term)
                            ->orWhere('r.email_cliente', 'LIKE', $term);
                    });
                })

                ->orderByDesc('r.id_reservacion')
                ->get();

            return view('Admin.ReservacionesActivas', [
                'reservaciones'        => $reservaciones,
                'sucursalSeleccionada' => $request->sucursal,
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', 'Error al cargar las reservaciones activas: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Retorna los detalles completos de una reservación activa (por código)
     */
    public function show($codigo)
    {
        try {
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
                    'r.no_vuelo',                              
                    DB::raw('DATEDIFF(r.fecha_fin, r.fecha_inicio) as dias'),
                    'c.codigo as categoria'  
                )
                ->where('r.codigo', $codigo)
                ->first();

            if (!$reservacion) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

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
            $reserv = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            if (!$reserv) {
                return back()->with('error', 'La reservación no existe.');
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->delete();

            return back()->with('success', 'Reservación eliminada correctamente.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Error al eliminar la reservación: ' . $e->getMessage());
        }
    }
}
