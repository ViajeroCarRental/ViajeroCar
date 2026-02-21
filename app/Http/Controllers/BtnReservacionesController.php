<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservacionUsuarioMail;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreReservacionRequest;
use App\Http\Requests\StoreReservacionLineaRequest;

class BtnReservacionesController extends Controller
{
    /**
     * 💾 Guarda una reservación real (solo pago en mostrador)
     * y envía correo automático al cliente y empresa.
     */


public function reservar(StoreReservacionRequest $request)
{
    try {
        // 1️⃣ Validación básica
        $validated = $request->validated();

        // 2️⃣ Generar código RES
        $fecha  = now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        $codigo = "RES-{$fecha}-{$random}";

        // 3️⃣ Calcular totales usando la CATEGORÍA (no el vehículo)
        $categoria = DB::table('categorias_carros')
            ->select('precio_dia')
            ->where('id_categoria', $validated['categoria_id'])
            ->first();

        $fechaInicio = Carbon::parse($validated['pickup_date']);
        $fechaFin    = Carbon::parse($validated['dropoff_date']);
        $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

        $precioDia = $categoria ? $categoria->precio_dia : 0;
        $subtotal  = $precioDia * $dias;
        $impuestos = round($subtotal * 0.16, 2);
        $total     = $subtotal + $impuestos;

        // 4️⃣ Estado fijo: pago pendiente en mostrador
        $estado = 'pendiente_pago';

        // 4.1️⃣ Determinar ciudad a partir de la sucursal de retiro
        $ciudadRetiro = null;

        if (!empty($validated['pickup_sucursal_id'])) {
            $ciudadRetiro = DB::table('sucursales')
                ->where('id_sucursal', $validated['pickup_sucursal_id'])
                ->value('id_ciudad');
        }

        if (!$ciudadRetiro) {
            // Fallback por si no viene sucursal o no se encuentra ciudad
            $ciudadRetiro = 1;
        }

        $ciudadEntrega = $ciudadRetiro;

        // 5️⃣ Insertar reservación
        $id = DB::table('reservaciones')->insertGetId([
            'id_usuario'       => null,
            'id_vehiculo'      => null, // 👉 se asigna después en el contrato
            'ciudad_retiro'    => $ciudadRetiro,
            'ciudad_entrega'   => $ciudadEntrega,
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
        Log::error('❌ Error creando reservación (mostrador): ' . $e->getMessage());

        return response()->json([
            'ok'      => false,
            'message' => 'Error interno al crear la reservación',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function reservarLinea(StoreReservacionLineaRequest $request)
{
    try {
        // 1️⃣ Validación de datos de la reserva + paypal_order_id obligatorio
        $validated = $request->validated();

        // 2️⃣ Código RES
        $fecha  = now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        $codigo = "RES-{$fecha}-{$random}";

        // 3️⃣ Totales (mismo cálculo que en reservar, pero usando CATEGORÍA)
        $categoria = DB::table('categorias_carros')
            ->select('precio_dia')
            ->where('id_categoria', $validated['categoria_id'])
            ->first();

        $fechaInicio = Carbon::parse($validated['pickup_date']);
        $fechaFin    = Carbon::parse($validated['dropoff_date']);
        $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

        $precioDia = $categoria ? $categoria->precio_dia : 0;
        $subtotal  = $precioDia * $dias;
        $impuestos = round($subtotal * 0.16, 2);
        $total     = $subtotal + $impuestos;

        // ============================================
        // 4️⃣ Validar la orden de PayPal en servidor
        // ============================================
        $paypalOrderId = $validated['paypal_order_id'];

        $mode = env('PAYPAL_MODE', 'live');
        if ($mode === 'live') {
            $clientId = env('PAYPAL_CLIENT_ID_LIVE');
            $secret   = env('PAYPAL_SECRET_LIVE');
            $baseUrl  = 'https://api-m.paypal.com';
        } else {
            $clientId = env('PAYPAL_CLIENT_ID_SANDBOX', env('PAYPAL_CLIENT_ID_LIVE'));
            $secret   = env('PAYPAL_SECRET_SANDBOX', env('PAYPAL_SECRET_LIVE'));
            $baseUrl  = 'https://api-m.sandbox.paypal.com';
        }

        if (!$clientId || !$secret) {
            Log::error('❌ Credenciales de PayPal incompletas en .env');
            return response()->json([
                'ok'      => false,
                'message' => 'Configuración de PayPal incompleta. Intenta más tarde.',
            ], 500);
        }

        // 4.1 Obtener access token
        $tokenResponse = Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post($baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$tokenResponse->ok()) {
            Log::error('❌ Error OAuth PayPal', ['body' => $tokenResponse->body()]);
            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo validar el pago con PayPal (OAuth).',
            ], 422);
        }

        $accessToken = $tokenResponse['access_token'] ?? null;
        if (!$accessToken) {
            Log::error('❌ PayPal sin access_token en respuesta OAuth', ['json' => $tokenResponse->json()]);
            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo obtener autorización de PayPal.',
            ], 422);
        }

        // 4.2 Consultar la orden en PayPal
        $orderResponse = Http::withToken($accessToken)
            ->get($baseUrl . '/v2/checkout/orders/' . $paypalOrderId);

        if (!$orderResponse->ok()) {
            Log::error('❌ No se pudo obtener la orden de PayPal', [
                'order_id' => $paypalOrderId,
                'body'     => $orderResponse->body(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo validar la orden de pago con PayPal.',
            ], 422);
        }

        $orderData = $orderResponse->json();
        $status    = $orderData['status'] ?? null;

        if ($status !== 'COMPLETED') {
            Log::warning('⚠️ Orden PayPal no completada', [
                'order_id' => $paypalOrderId,
                'status'   => $status,
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'El pago aún no está completado en PayPal.',
            ], 422);
        }

        // 4.3 Validar monto y moneda
        $purchaseUnits = $orderData['purchase_units'][0] ?? null;
        $amountData    = $purchaseUnits['amount'] ?? null;
        $amountValue   = $amountData['value'] ?? null;
        $currencyCode  = $amountData['currency_code'] ?? null;

        $expectedTotal = number_format($total, 2, '.', '');

        if ($currencyCode !== 'MXN' || $amountValue != $expectedTotal) {
            Log::warning('⚠️ Desajuste entre total local y PayPal', [
                'order_id'      => $paypalOrderId,
                'paypal_value'  => $amountValue,
                'paypal_curr'   => $currencyCode,
                'expectedTotal' => $expectedTotal,
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'El monto del pago no coincide con la reservación.',
            ], 422);
        }

        // ============================================
        // 5️⃣ Determinar ciudad a partir de sucursal
        // ============================================
        $ciudadRetiro = null;

        if (!empty($validated['pickup_sucursal_id'])) {
            $ciudadRetiro = DB::table('sucursales')
                ->where('id_sucursal', $validated['pickup_sucursal_id'])
                ->value('id_ciudad');
        }

        if (!$ciudadRetiro) {
            // Fallback si no hay sucursal o ciudad
            $ciudadRetiro = 1;
        }

        $ciudadEntrega = $ciudadRetiro;

        // ============================================
        // 6️⃣ Insertar reservación confirmada
        // ============================================
        $id = DB::table('reservaciones')->insertGetId([
            'id_usuario'       => null,
            'id_vehiculo'      => null, // 👉 se asigna después en el contrato
            'ciudad_retiro'    => $ciudadRetiro,
            'ciudad_entrega'   => $ciudadEntrega,
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
            'paypal_order_id'  => $paypalOrderId,
            'status_pago'      => 'Pagado',
            'metodo_pago'      => 'en_linea',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 7️⃣ Enviar correo con plantilla (PAGO EN LÍNEA)
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!empty($reservacion->email_cliente)) {
            Mail::to($reservacion->email_cliente)
                ->cc(env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com'))
                ->send(new ReservacionUsuarioMail($reservacion, 'en_linea'));
        }

        // 8️⃣ Respuesta JSON
        return response()->json([
            'ok'        => true,
            'folio'     => $codigo,
            'id'        => $id,
            'subtotal'  => $subtotal,
            'impuestos' => $impuestos,
            'total'     => $total,
            'estado'    => 'confirmada',
            'message'   => 'Pago validado con PayPal y reserva confirmada correctamente.',
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ Error en reservarLinea: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'ok'      => false,
            'message' => 'Error interno al procesar la reserva en línea.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


}
