<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BtnReservacionesController extends Controller
{
    /**
     * Guarda una reservación real (Pago en mostrador o en línea)
     * y envía correo automático solo cuando el pago está confirmado.
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
                'addons'              => 'nullable|array',
                'metodo_pago'         => 'nullable|string|in:mostrador,en_linea'
            ]);

            // 2️⃣ Generar código RES
            $fecha = now()->format('Ymd');
            $random = strtoupper(Str::random(5));
            $codigo = "RES-{$fecha}-{$random}";

            // 3️⃣ Calcular totales
            $vehiculo = DB::table('vehiculos')
                ->select('precio_dia', 'id_ciudad as ciudad_retiro')
                ->where('id_vehiculo', $validated['vehiculo_id'])
                ->first();

            $fechaInicio = Carbon::parse($validated['pickup_date']);
            $fechaFin = Carbon::parse($validated['dropoff_date']);
            $dias = max(1, $fechaInicio->diffInDays($fechaFin));

            $subtotal = $vehiculo ? ($vehiculo->precio_dia * $dias) : 0;
            $impuestos = round($subtotal * 0.16, 2);
            $total = $subtotal + $impuestos;

            // 4️⃣ Determinar el estado según el método de pago
            $metodoPago = $validated['metodo_pago'] ?? 'mostrador';
            $estado = $metodoPago === 'en_linea' ? 'confirmada' : 'pendiente_pago';

            // 5️⃣ Insertar reservación
            $id = DB::table('reservaciones')->insertGetId([
                'id_usuario'         => null,
                'id_vehiculo'        => $validated['vehiculo_id'],
                'ciudad_retiro'      => $vehiculo ? $vehiculo->ciudad_retiro : 1,
                'ciudad_entrega'     => $vehiculo ? $vehiculo->ciudad_retiro : 1,
                'sucursal_retiro'    => $validated['pickup_sucursal_id'] ?? null,
                'sucursal_entrega'   => $validated['dropoff_sucursal_id'] ?? null,
                'fecha_inicio'       => $validated['pickup_date'],
                'hora_retiro'        => $validated['pickup_time'],
                'fecha_fin'          => $validated['dropoff_date'],
                'hora_entrega'       => $validated['dropoff_time'],
                'estado'             => $estado,
                'subtotal'           => $subtotal,
                'impuestos'          => $impuestos,
                'total'              => $total,
                'moneda'             => 'MXN',
                'no_vuelo'           => $validated['vuelo'] ?? null,
                'codigo'             => $codigo,
                'nombre_cliente'     => $validated['nombre'] ?? null,
                'email_cliente'      => $validated['email'] ?? null,
                'telefono_cliente'   => $validated['telefono'] ?? null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // 6️⃣ Si el método de pago es en línea, enviar correo
            if ($metodoPago === 'en_linea') {
                $mensaje = "📩 CONFIRMACIÓN DE RESERVA\n\n";
                $mensaje .= "Código de reserva: {$codigo}\n\n";
                $mensaje .= "👤 Cliente:\n";
                $mensaje .= "Nombre: " . ($validated['nombre'] ?? 'No especificado') . "\n";
                $mensaje .= "Correo: " . ($validated['email'] ?? '-') . "\n";
                $mensaje .= "Teléfono: " . ($validated['telefono'] ?? '-') . "\n";
                $mensaje .= "Vuelo: " . ($validated['vuelo'] ?? '-') . "\n\n";
                $mensaje .= "📅 Fechas:\n";
                $mensaje .= "Entrega: {$validated['pickup_date']} {$validated['pickup_time']}\n";
                $mensaje .= "Devolución: {$validated['dropoff_date']} {$validated['dropoff_time']}\n\n";
                $mensaje .= "💰 Montos:\n";
                $mensaje .= "Subtotal: $" . number_format($subtotal, 2) . " MXN\n";
                $mensaje .= "Impuestos: $" . number_format($impuestos, 2) . " MXN\n";
                $mensaje .= "Total pagado: $" . number_format($total, 2) . " MXN\n\n";
                $mensaje .= "📝 Notas importantes:\n";
                $mensaje .= "- Los seguros obligatorios no están incluidos en este monto.\n";
                $mensaje .= "- Se cotizan y confirman con un agente de Viajero Car Rental.\n";
                $mensaje .= "- Tarifas y disponibilidad sujetas a cambio sin previo aviso.\n";
                $mensaje .= "- Se requiere tarjeta de crédito física del titular al recoger el vehículo.\n\n";
                $mensaje .= "📆 Fecha de registro: " . now()->format('d/m/Y H:i:s') . "\n";

                $correoCliente = $validated['email'] ?? null;
                $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');

                Mail::raw($mensaje, function ($msg) use ($correoCliente, $correoEmpresa, $codigo) {
                    if ($correoCliente) {
                        $msg->to($correoCliente)
                            ->cc($correoEmpresa)
                            ->subject("Confirmación de reserva {$codigo} - Viajero Car Rental");
                    } else {
                        $msg->to($correoEmpresa)
                            ->subject("Nueva reserva {$codigo} - Viajero Car Rental");
                    }
                });
            }

            // 7️⃣ Respuesta JSON
            return response()->json([
                'ok' => true,
                'folio' => $codigo,
                'id' => $id,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'estado' => $estado,
                'message' => $metodoPago === 'en_linea'
                    ? 'Reservación confirmada y correo enviado correctamente.'
                    : 'Reservación creada (pago pendiente en mostrador).',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error creando reservación: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Error interno al crear la reservación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reservarLinea(Request $request)
    {
        $request->merge(['metodo_pago' => 'en_linea']);
        return $this->reservar($request);
    }


}
