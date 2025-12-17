<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservacionesActivasController extends Controller
{
    /**
     * 📋 Muestra todas las reservaciones activas (no canceladas ni expiradas)
     */
    public function index(Request $request)
    {
        try {
            // 🔹 Tomamos los filtros del request
            $sucursal = $request->input('sucursal');
            $codigo   = trim($request->input('codigo')); // 🔴 filtro exclusivo para código
            $search   = trim($request->input('q'));      // 🔵 filtro para nombre o correo

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
                    'r.no_vuelo',              // número de vuelo
                    'c.codigo as categoria'    // C, D, E, H, etc.
                )
                // solo activas
                ->whereNotIn('r.estado', ['cancelada', 'expirada'])

                // 🔽 Filtro opcional por sucursal_retiro (Aeropuerto / Central / Central Park)
                ->when($sucursal, function ($q, $sucursal) {
                    $q->where('r.sucursal_retiro', $sucursal);
                })

                // 🟥 1) FILTRO INDEPENDIENTE POR CÓDIGO (coincidencia desde el inicio)
                ->when($codigo, function ($q, $codigo) {
                    $q->where('r.codigo', 'LIKE', $codigo . '%');
                })

                // 🟦 2) FILTRO INDEPENDIENTE POR NOMBRE O CORREO (desde el inicio)
                ->when($search, function ($q, $search) {
                    $term = $search . '%';

                    $q->where(function ($sub) use ($term) {
                        $sub->where('r.nombre_cliente', 'LIKE', $term)
                            ->orWhere('r.email_cliente', 'LIKE', $term);
                    });
                })

                // ✅ ORDEN: más próxima -> más lejana
                ->orderBy('r.fecha_inicio', 'asc')
                // ✅ si empatan en fecha, ordena por hora
                ->orderBy('r.hora_retiro', 'asc')

                ->get();

            return view('Admin.ReservacionesActivas', [
                'reservaciones'        => $reservaciones,
                'sucursalSeleccionada' => $sucursal,
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

    /**
 * ✏️ Actualiza SOLO: nombre, correo, teléfono, salida/entrega (fecha+hora)
 */
public function updateDatos(Request $request, $id)
{
    try {
        $validated = $request->validate([
            'nombre_cliente'   => 'required|string|max:120',
            'email_cliente'    => 'required|email|max:120',
            'telefono_cliente' => 'required|string|max:40',

            'fecha_inicio'     => 'required|date',
            'hora_retiro'      => 'nullable|string|max:10',

            'fecha_fin'        => 'required|date|after_or_equal:fecha_inicio',
            'hora_entrega'     => 'nullable|string|max:10',
        ]);

        // 🔹 Verificar reservación
        $reserv = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!$reserv) {
            return response()->json([
                'success' => false,
                'message' => 'Reservación no encontrada.'
            ], 404);
        }

        // 🔹 Actualizar datos
        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'nombre_cliente'   => $validated['nombre_cliente'],
                'email_cliente'    => $validated['email_cliente'],
                'telefono_cliente' => $validated['telefono_cliente'],
                'fecha_inicio'     => $validated['fecha_inicio'],
                'hora_retiro'      => $validated['hora_retiro'],
                'fecha_fin'        => $validated['fecha_fin'],
                'hora_entrega'     => $validated['hora_entrega'],
                'updated_at'       => now(),
            ]);

        // 🔹 Reconsultar (FUENTE ÚNICA DE VERDAD)
        $r = DB::table('reservaciones as r')
            ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
            ->select(
                'r.codigo',
                'r.nombre_cliente',
                'r.email_cliente',
                'r.telefono_cliente',
                'r.fecha_inicio',
                'r.hora_retiro',
                'r.fecha_fin',
                'r.hora_entrega',
                'r.subtotal',
                'r.impuestos',
                'r.total',
                'r.no_vuelo',
                'r.moneda',
                'c.nombre as categoria'
            )
            ->where('r.id_reservacion', $id)
            ->first();

        // 🔹 Correos
        $correoCliente = trim((string) $r->email_cliente);
        $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');
        $moneda = $r->moneda ?? 'MXN';

        if ($correoCliente && !filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
            Log::warning("⚠️ Correo inválido al guardar {$r->codigo}: {$correoCliente}");
            $correoCliente = null;
        }

        // 🔹 Construir mensaje
        $mensaje  = "📩 CONFIRMACIÓN DE RESERVA (ACTUALIZACIÓN)\n\n";
        $mensaje .= "Código de reserva: {$r->codigo}\n";
        $mensaje .= "Categoría: " . ($r->categoria ?? '-') . "\n\n";

        $mensaje .= "👤 Cliente:\n";
        $mensaje .= "Nombre: {$r->nombre_cliente}\n";
        $mensaje .= "Correo: {$r->email_cliente}\n";
        $mensaje .= "Teléfono: {$r->telefono_cliente}\n";
        $mensaje .= "Vuelo: " . ($r->no_vuelo ?? '-') . "\n\n";

        $mensaje .= "📅 Fechas:\n";
        $mensaje .= "Entrega: {$r->fecha_inicio}" . ($r->hora_retiro ? " {$r->hora_retiro}" : "") . "\n";
        $mensaje .= "Devolución: {$r->fecha_fin}" . ($r->hora_entrega ? " {$r->hora_entrega}" : "") . "\n\n";

        $mensaje .= "💰 Montos:\n";
        $mensaje .= "Subtotal: $" . number_format($r->subtotal, 2) . " {$moneda}\n";
        $mensaje .= "Impuestos: $" . number_format($r->impuestos, 2) . " {$moneda}\n";
        $mensaje .= "Total: $" . number_format($r->total, 2) . " {$moneda}\n\n";

        $mensaje .= "📆 Enviado: " . now()->format('d/m/Y H:i:s');

        // 🔹 Enviar correo
        Mail::raw($mensaje, function ($msg) use ($correoCliente, $correoEmpresa, $r) {
            if ($correoCliente) {
                $msg->to($correoCliente)
                    ->cc($correoEmpresa)
                    ->subject("Confirmación de reserva {$r->codigo} - Viajero Car Rental");
            } else {
                $msg->to($correoEmpresa)
                    ->subject("Reserva {$r->codigo} (correo cliente inválido)");
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Datos actualizados y correo enviado correctamente.'
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ Error updateDatos+correo: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error interno al actualizar y enviar correo.'
        ], 500);
    }
}




}
