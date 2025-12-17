<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservacionUsuarioMail;

class BtnReservacionesController extends Controller
{
    /**
     * 💾 Guarda una reservación real (solo pago en mostrador)
     * y envía correo automático al cliente y empresa.
     */


public function reservar(Request $request)
{
    try {
        // 1️⃣ Validación básica
        $validated = $request->validate([
            'vehiculo_id'         => 'required|integer',
            'pickup_date'         => 'required|date',
            'pickup_time'         => 'required',
            'dropoff_date'        => 'required|date',
            'dropoff_time'        => 'required',
            'pickup_sucursal_id'  => 'nullable|integer',
            'dropoff_sucursal_id' => 'nullable|integer',
            'nombre'              => 'nullable|string|max:120',
            'email'               => 'nullable|string|max:120',
            'telefono'            => 'nullable|string|max:40',
            'vuelo'               => 'nullable|string|max:40',
            'addons'              => 'nullable|array'
        ]);

        // 2️⃣ Generar código RES
        $fecha  = now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        $codigo = "RES-{$fecha}-{$random}";

        // 3️⃣ Calcular totales
        $vehiculo = DB::table('vehiculos')
            ->select('precio_dia', 'id_ciudad as ciudad_retiro')
            ->where('id_vehiculo', $validated['vehiculo_id'])
            ->first();

        $fechaInicio = Carbon::parse($validated['pickup_date']);
        $fechaFin    = Carbon::parse($validated['dropoff_date']);
        $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

        $subtotal  = $vehiculo ? ($vehiculo->precio_dia * $dias) : 0;
        $impuestos = round($subtotal * 0.16, 2);
        $total     = $subtotal + $impuestos;

        // 4️⃣ Estado fijo: pago pendiente en mostrador
        $estado = 'pendiente_pago';

        // 5️⃣ Insertar reservación
        $id = DB::table('reservaciones')->insertGetId([
            'id_usuario'       => null,
            'id_vehiculo'      => $validated['vehiculo_id'],
            'ciudad_retiro'    => $vehiculo ? $vehiculo->ciudad_retiro : 1,
            'ciudad_entrega'   => $vehiculo ? $vehiculo->ciudad_retiro : 1,
            'sucursal_retiro'  => $validated['pickup_sucursal_id'] ?? null,
            'sucursal_entrega' => $validated['dropoff_sucursal_id'] ?? null,
            'fecha_inicio'     => $validated['pickup_date'],
            'hora_retiro'      => $validated['pickup_time'],
            'fecha_fin'        => $validated['dropoff_date'],
            'hora_entrega'     => $validated['dropoff_time'],
            'estado'           => $estado,
            'subtotal'         => $subtotal,
            'impuestos'        => $impuestos,
            'total'            => $total,
            'moneda'           => 'MXN',
            'no_vuelo'         => $validated['vuelo'] ?? null,
            'codigo'           => $codigo,
            'nombre_cliente'   => $validated['nombre'] ?? null,
            'email_cliente'    => $validated['email'] ?? null,
            'telefono_cliente' => $validated['telefono'] ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 6️⃣ Enviar correo con plantilla (PAGO EN MOSTRADOR)
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!empty($reservacion->email_cliente)) {
            Mail::to($reservacion->email_cliente)
                ->cc(env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com'))
                ->send(new ReservacionUsuarioMail($reservacion, 'mostrador'));
        }

        // 7️⃣ Respuesta JSON
        return response()->json([
            'ok'        => true,
            'folio'     => $codigo,
            'id'        => $id,
            'subtotal'  => $subtotal,
            'impuestos' => $impuestos,
            'total'     => $total,
            'estado'    => $estado,
            'message'   => 'Reservación creada con éxito y correo enviado.',
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ Error creando reservación: ' . $e->getMessage());

        return response()->json([
            'ok'      => false,
            'message' => 'Error interno al crear la reservación',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    public function reservarLinea(Request $request)
{
    try {
        // 1️⃣ Validación
        $validated = $request->validate([
            'vehiculo_id'         => 'required|integer',
            'pickup_date'         => 'required|date',
            'pickup_time'         => 'required',
            'dropoff_date'        => 'required|date',
            'dropoff_time'        => 'required',
            'pickup_sucursal_id'  => 'nullable|integer',
            'dropoff_sucursal_id' => 'nullable|integer',
            'nombre'              => 'nullable|string|max:120',
            'email'               => 'nullable|string|max:120',
            'telefono'            => 'nullable|string|max:40',
            'vuelo'               => 'nullable|string|max:40',
            'addons'              => 'nullable|array',
            'paypal_order_id'     => 'nullable|string',
            'status_pago'         => 'nullable|string',
        ]);

        // 2️⃣ Código RES
        $fecha  = now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        $codigo = "RES-{$fecha}-{$random}";

        // 3️⃣ Totales
        $vehiculo = DB::table('vehiculos')
            ->select('precio_dia', 'id_ciudad as ciudad_retiro')
            ->where('id_vehiculo', $validated['vehiculo_id'])
            ->first();

        $fechaInicio = Carbon::parse($validated['pickup_date']);
        $fechaFin    = Carbon::parse($validated['dropoff_date']);
        $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

        $subtotal  = $vehiculo ? ($vehiculo->precio_dia * $dias) : 0;
        $impuestos = round($subtotal * 0.16, 2);
        $total     = $subtotal + $impuestos;

        // 4️⃣ Insertar reservación confirmada
        $id = DB::table('reservaciones')->insertGetId([
            'id_usuario'       => null,
            'id_vehiculo'      => $validated['vehiculo_id'],
            'ciudad_retiro'    => $vehiculo ? $vehiculo->ciudad_retiro : 1,
            'ciudad_entrega'   => $vehiculo ? $vehiculo->ciudad_retiro : 1,
            'sucursal_retiro'  => $validated['pickup_sucursal_id'] ?? null,
            'sucursal_entrega' => $validated['dropoff_sucursal_id'] ?? null,
            'fecha_inicio'     => $validated['pickup_date'],
            'hora_retiro'      => $validated['pickup_time'],
            'fecha_fin'        => $validated['dropoff_date'],
            'hora_entrega'     => $validated['dropoff_time'],
            'estado'           => 'confirmada',
            'subtotal'         => $subtotal,
            'impuestos'        => $impuestos,
            'total'            => $total,
            'moneda'           => 'MXN',
            'no_vuelo'         => $validated['vuelo'] ?? null,
            'codigo'           => $codigo,
            'nombre_cliente'   => $validated['nombre'] ?? null,
            'email_cliente'    => $validated['email'] ?? null,
            'telefono_cliente' => $validated['telefono'] ?? null,
            'paypal_order_id'  => $validated['paypal_order_id'] ?? null,
            'status_pago'      => 'Pagado',
            'metodo_pago'      => 'en_linea',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 5️⃣ Enviar correo con plantilla (PAGO EN LÍNEA)
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!empty($reservacion->email_cliente)) {
            Mail::to($reservacion->email_cliente)
                ->cc(env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com'))
                ->send(new ReservacionUsuarioMail($reservacion, 'en_linea'));
        }

        // 6️⃣ Respuesta JSON
        return response()->json([
            'ok'        => true,
            'folio'     => $codigo,
            'id'        => $id,
            'subtotal'  => $subtotal,
            'impuestos' => $impuestos,
            'total'     => $total,
            'estado'    => 'confirmada',
            'message'   => 'Pago completado y reserva confirmada correctamente.',
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ Error en reservarLinea: ' . $e->getMessage());

        return response()->json([
            'ok'      => false,
            'message' => 'Error interno al procesar la reserva en línea.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}
