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
}
