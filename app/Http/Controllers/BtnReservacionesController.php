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
     * Guarda una reservación real
     */
    public function reservar(StoreReservacionRequest $request)
    {
        try {
            $validated = $request->validated();

            // Delegar todos los cálculos complejos a la función centralizada
            $data = $this->procesarCalculosYResumen($validated, 'mostrador');

            // Generar folio único
            $codigo = $this->generarFolioReservacionUnico();

            // Insertar reservación principal
            $id = DB::table('reservaciones')->insertGetId([
                'id_usuario'       => null,
                'id_vehiculo'      => null,
                'id_categoria'     => $validated['categoria_id'],
                'tarifa_base'      => $data['precioDia'],
                'ciudad_retiro'    => $data['ciudadRetiro'],
                'ciudad_entrega'   => $data['ciudadEntrega'],
                'sucursal_retiro'  => $validated['pickup_sucursal_id'] ?? null,
                'sucursal_entrega' => $validated['dropoff_sucursal_id'] ?? null,
                'fecha_inicio'     => $validated['pickup_date'],
                'hora_retiro'      => $validated['pickup_time'],
                'fecha_fin'        => $validated['dropoff_date'],
                'hora_entrega'     => $validated['dropoff_time'],
                'estado'           => 'pendiente_pago',
                'subtotal'         => $data['subtotal'],
                'impuestos'        => $data['impuestos'],
                'total'            => $data['total'],
                'moneda'           => 'MXN',
                'no_vuelo'         => $validated['vuelo'] ?? null,
                'codigo'           => $codigo,
                'nombre_cliente'   => $validated['nombre'] ?? null,
                'email_cliente'    => $validated['email'] ?? null,
                'telefono_cliente' => $validated['telefono'] ?? null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Insertar servicios (Addons + Dropoff)
            $this->guardarServiciosReservacion($id, $data['serviciosAInsertar']);

            // Enviar correo
            $this->enviarCorreoConfirmacion($id, 'mostrador', $data);

            return response()->json([
                'ok'        => true,
                'folio'     => $codigo,
                'id'        => $id,
                'subtotal'  => $data['subtotal'],
                'impuestos' => $data['impuestos'],
                'total'     => $data['total'],
                'estado'    => 'pendiente_pago',
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

    /**
     * Validar pago en PayPal y guardar reservación en línea
     */
    public function reservarLinea(StoreReservacionLineaRequest $request)
    {
        try {
            $validated = $request->validated();

            // Delegar todos los cálculos complejos a la función centralizada
            $data = $this->procesarCalculosYResumen($validated, 'linea');

            // Validar pago con PayPal
            $paypalValidado = $this->validarPagoPayPal($validated['paypal_order_id'], $data['total']);

            if (!$paypalValidado['ok']) {
                return response()->json($paypalValidado, 422);
            }

            // Generar folio único
            $codigo = $this->generarFolioReservacionUnico();

            // Insertar reservación principal confirmada
            $id = DB::table('reservaciones')->insertGetId([
                'id_usuario'       => null,
                'id_vehiculo'      => null,
                'id_categoria'     => $validated['categoria_id'],
                'tarifa_base'      => $data['precioDia'],
                'ciudad_retiro'    => $data['ciudadRetiro'],
                'ciudad_entrega'   => $data['ciudadEntrega'],
                'sucursal_retiro'  => $validated['pickup_sucursal_id'] ?? null,
                'sucursal_entrega' => $validated['dropoff_sucursal_id'] ?? null,
                'fecha_inicio'     => $validated['pickup_date'],
                'hora_retiro'      => $validated['pickup_time'],
                'fecha_fin'        => $validated['dropoff_date'],
                'hora_entrega'     => $validated['dropoff_time'],
                'estado'           => 'confirmada',
                'subtotal'         => $data['subtotal'],
                'impuestos'        => $data['impuestos'],
                'total'            => $data['total'],
                'moneda'           => 'MXN',
                'no_vuelo'         => $validated['vuelo'] ?? null,
                'codigo'           => $codigo,
                'nombre_cliente'   => $validated['nombre'] ?? null,
                'email_cliente'    => $validated['email'] ?? null,
                'telefono_cliente' => $validated['telefono'] ?? null,
                'paypal_order_id'  => $validated['paypal_order_id'],
                'status_pago'      => 'Pagado',
                'metodo_pago'      => 'en_linea',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Insertar servicios (Addons + Dropoff)
            $this->guardarServiciosReservacion($id, $data['serviciosAInsertar']);

            // Enviar correo
            $this->enviarCorreoConfirmacion($id, 'en_linea', $data);

            return response()->json([
                'ok'        => true,
                'folio'     => $codigo,
                'id'        => $id,
                'subtotal'  => $data['subtotal'],
                'impuestos' => $data['impuestos'],
                'total'     => $data['total'],
                'estado'    => 'confirmada',
                'message'   => 'Pago validado con PayPal y reserva confirmada correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Error en reservarLinea: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'ok'      => false,
                'message' => 'Error interno al procesar la reserva en línea.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Realiza todos los cálculos (Días, Addons, Dropoff, Impuestos)
     * y genera la estructura de datos que comparten Mostrador, PayPal y los Emails.
     */
    private function procesarCalculosYResumen(array $validated, string $tipoPlan): array
    {
        // Días de Renta y Tolerancia
        $fechaInicio = Carbon::parse($validated['pickup_date'] . ' ' . $validated['pickup_time']);
        $fechaFin    = Carbon::parse($validated['dropoff_date'] . ' ' . $validated['dropoff_time']);

        $horasTotales = $fechaInicio->diffInHours($fechaFin);
        $diasBase     = intdiv($horasTotales, 24);
        $horasExtra   = $horasTotales % 24;
        $dias         = ($horasExtra > 1) ? $diasBase + 1 : max(1, $diasBase);

        // Tarifa Base
        $categoria = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
            ->where('id_categoria', $validated['categoria_id'])
            ->first();

        $precioDia = $categoria ? (float)$categoria->precio_dia : 0.0;

        // Si eligen mostrador, el precio diario se infla un 15%
        if ($tipoPlan === 'mostrador' && $precioDia > 0) {
            $precioDia = round($precioDia * 1.15);
        }

        $subtotalBase = $precioDia * $dias;

        // Servicios Extras (Addons)
        $addonsMap = [];
        if (!empty($validated['addons'])) {
            foreach (explode(',', $validated['addons']) as $pair) {
                $pair = trim($pair);
                if (preg_match('/^(\d+)\s*:\s*(\d+)$/', $pair, $matches) && (int)$matches[1] > 0 && (int)$matches[2] > 0) {
                    $addonsMap[(int)$matches[1]] = ($addonsMap[(int)$matches[1]] ?? 0) + (int)$matches[2];
                }
            }
        }

        $capacidadTanque = (float) (DB::table('vehiculos')
            ->where('id_categoria', $validated['categoria_id'])
            ->where('id_estatus', 1)
            ->max('capacidad_tanque') ?? 50);

        $extrasSubtotal = 0.0;
        $serviciosAInsertar = []; // Array listo para un `insert` masivo limpio
        $extrasParaCorreo = collect(); // Para la plantilla

        if (!empty($addonsMap)) {
            $serviciosDB = DB::table('servicios')->whereIn('id_servicio', array_keys($addonsMap))->get();

            foreach ($serviciosDB as $srv) {
                if ($srv->id_servicio == 11) continue; // Saltamos dropoff si viene inyectado por error

                $cantidad = $addonsMap[$srv->id_servicio] ?? 0;
                $precioBase = (float)$srv->precio;
                $tipoCobro = strtolower((string)$srv->tipo_cobro);

                $cantParaDB = $cantidad;
                $precioUnitarioDB = $precioBase;
                $lineTotal = 0;

                // Lógica de cálculo
                if ($srv->id_servicio == 1) { // Gasolina
                    $lineTotal = $precioBase * $capacidadTanque;
                    $cantParaDB = max(1, (int)round($capacidadTanque));
                } elseif ($tipoCobro === 'por_tanque') {
                    $lineTotal = $precioBase * $capacidadTanque * $cantidad;
                    $cantParaDB = max(1, (int)round($capacidadTanque)) * $cantidad;
                } elseif ($tipoCobro === 'por_evento') {
                    $lineTotal = $precioBase * $cantidad;
                } else { // Por Día
                    $lineTotal = $precioBase * $cantidad * $dias;
                    $precioUnitarioDB = $precioBase * $dias;
                }

                $extrasSubtotal += $lineTotal;

                $serviciosAInsertar[] = [
                    'id_servicio'     => $srv->id_servicio,
                    'cantidad'        => $cantParaDB,
                    'precio_unitario' => $precioUnitarioDB,
                ];

                // Formateamos para el correo en memoria 
                $srv->cantidad = $cantParaDB;
                $srv->precio_unitario = $precioUnitarioDB;
                $srv->total = $lineTotal;
                $extrasParaCorreo->push($srv);
            }
        }

        // Dropoff Dinámico
        $montoDropoff = 0;
        if (!empty($validated['pickup_sucursal_id']) && !empty($validated['dropoff_sucursal_id']) && $validated['pickup_sucursal_id'] != $validated['dropoff_sucursal_id']) {
            $nombreDestino = DB::table('sucursales')->where('id_sucursal', $validated['dropoff_sucursal_id'])->value('nombre');

            if ($nombreDestino) {
                $km = DB::table('ubicaciones_servicio')->where('destino', $nombreDestino)->where('activo', true)->value('km') ?? 0;
                $costoKm = DB::table('categoria_costo_km')->where('id_categoria', $validated['categoria_id'])->where('activo', true)->value('costo_km') ?? 0;
                $montoDropoff = (float)$km * (float)$costoKm;

                if ($montoDropoff > 0) {
                    $extrasSubtotal += $montoDropoff;
                    $serviciosAInsertar[] = [
                        'id_servicio'     => 11,
                        'cantidad'        => 1,
                        'precio_unitario' => $montoDropoff,
                    ];

                    $extrasParaCorreo->push((object)[
                        'id_servicio' => 11,
                        'nombre' => 'Cargo por Devolución (Drop-off)',
                        'descripcion' => 'Entrega en sucursal diferente',
                        'cantidad' => 1,
                        'precio_unitario' => $montoDropoff,
                        'total' => $montoDropoff
                    ]);
                }
            }
        }

        // Gran Total
        $subtotal  = $subtotalBase + $extrasSubtotal;
        $impuestos = round($subtotal * 0.16, 2);
        $total     = $subtotal + $impuestos;

        // Determinar Ciudad para BD
        $ciudadRetiro = 1; // Fallback
        if (!empty($validated['pickup_sucursal_id'])) {
            $ciudadRetiro = DB::table('sucursales')->where('id_sucursal', $validated['pickup_sucursal_id'])->value('id_ciudad') ?? 1;
        }

        // Estructurar Ficha "Tu Auto"
        $predeterminados = [
            'C'  => ['pax' => 5,  'small' => 2, 'big' => 1],
            'D'  => ['pax' => 5,  'small' => 2, 'big' => 1],
            'E'  => ['pax' => 5,  'small' => 2, 'big' => 2],
            'F'  => ['pax' => 5,  'small' => 2, 'big' => 2],
            'IC' => ['pax' => 5,  'small' => 2, 'big' => 2],
            'I'  => ['pax' => 5,  'small' => 3, 'big' => 2],
            'IB' => ['pax' => 7,  'small' => 3, 'big' => 2],
            'M'  => ['pax' => 7,  'small' => 4, 'big' => 2],
            'L'  => ['pax' => 13, 'small' => 4, 'big' => 3],
            'H'  => ['pax' => 5,  'small' => 3, 'big' => 2],
            'HI' => ['pax' => 5,  'small' => 3, 'big' => 2],
        ];

        $codigoCat = strtoupper(trim((string)($categoria->codigo ?? '')));
        $cap = $predeterminados[$codigoCat] ?? ['pax' => 5, 'small' => 2, 'big' => 1];

        $singular = rtrim(mb_strtoupper(trim((string)($categoria->nombre ?? ''))), 'S');
        $tuAuto = [
            'titulo'      => trim((string)($categoria->descripcion ?? 'Auto o similar')),
            'subtitulo'   => $singular . " | CATEGORÍA " . ($codigoCat ?: '-'),
            'pax'         => (int)$cap['pax'],
            'small'       => (int)$cap['small'],
            'big'         => (int)$cap['big'],
            'transmision' => 'Transmisión manual o automática',
            'tech'        => 'Apple CarPlay | Android Auto',
            'incluye'     => 'KM ilimitados | Reelevo de Responsabilidad (LI)',
        ];

        // Enlaces de imagen
        $catImages = [
            1 => 'img/aveo.png',
            2 => 'img/virtus.png',
            3 => 'img/jetta.png',
            4 => 'img/camry.png',
            5 => 'img/renegade.png',
            6 => 'img/taos.png',
            7 => 'img/avanza.png',
            8 => 'img/Odyssey.png',
            9 => 'img/Hiace.png',
            10 => 'img/Frontier.png',
            11 => 'img/Tacoma.png',
        ];
        $catId = (int)($categoria->id_categoria ?? 0);
        $imgCategoria = rtrim(config('app.url', 'https://viajerocar-production.up.railway.app'), '/') . '/' . ltrim($catImages[$catId] ?? 'img/categorias/placeholder.png', '/');

        // Devolvemos el gran diccionario listo para usar
        return [
            'categoria'          => $categoria,
            'precioDia'          => $precioDia,
            'ciudadRetiro'       => $ciudadRetiro,
            'ciudadEntrega'      => $ciudadRetiro, // Misma ciudad por defecto
            'subtotal'           => $subtotal,
            'impuestos'          => $impuestos,
            'total'              => $total,
            'serviciosAInsertar' => $serviciosAInsertar,
            'extrasParaCorreo'   => $extrasParaCorreo,
            'opcionesRentaTotal' => round($extrasSubtotal, 2),
            'tuAuto'             => $tuAuto,
            'imgCategoria'       => $imgCategoria
        ];
    }

    /**
     * Helper para insertar todos los servicios adicionales calculados
     */
    private function guardarServiciosReservacion(int $reservacionId, array $servicios): void
    {
        if (empty($servicios)) return;

        $insertData = [];
        foreach ($servicios as $srv) {
            $insertData[] = [
                'id_reservacion'  => $reservacionId,
                'id_servicio'     => $srv['id_servicio'],
                'id_contrato'     => null,
                'cantidad'        => $srv['cantidad'],
                'precio_unitario' => $srv['precio_unitario'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        DB::table('reservacion_servicio')->insert($insertData);
    }

    /**
     * Helper centralizado para enviar correos y no repetir la lógica de las sucursales
     */
    private function enviarCorreoConfirmacion(int $reservacionId, string $tipoPlanCorreo, array $data): void
    {
        $reservacion = DB::table('reservaciones')->where('id_reservacion', $reservacionId)->first();
        if (empty($reservacion->email_cliente)) return;

        $nombresSucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
            ->select('s.id_sucursal', 's.nombre as nombre_sucursal', 'c.nombre as nombre_ciudad')
            ->whereIn('s.id_sucursal', array_filter([$reservacion->sucursal_retiro, $reservacion->sucursal_entrega]))
            ->get()->keyBy('id_sucursal');

        $infoRetiro = $nombresSucursales->get($reservacion->sucursal_retiro);
        $infoEntrega = $nombresSucursales->get($reservacion->sucursal_entrega);

        $lugarRetiro  = $infoRetiro ? "{$infoRetiro->nombre_ciudad} - {$infoRetiro->nombre_sucursal}" : '-';
        $lugarEntrega = $infoEntrega ? "{$infoEntrega->nombre_ciudad} - {$infoEntrega->nombre_sucursal}" : '-';

        Mail::to($reservacion->email_cliente)
            ->cc(env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocar-rental.com'))
            ->send(new ReservacionUsuarioMail(
                $reservacion,
                $tipoPlanCorreo,
                $data['categoria'],
                $data['extrasParaCorreo'],
                $lugarRetiro,
                $lugarEntrega,
                $data['imgCategoria'],
                $data['opcionesRentaTotal'],
                $data['tuAuto']
            ));
    }

    /**
     * Consulta a la API de PayPal para verificar que la orden existe,
     * está pagada, y los montos cuadran perfectamente.
     */
    private function validarPagoPayPal(string $paypalOrderId, float $totalEsperado): array
    {
        $mode = env('PAYPAL_MODE', 'live');
        $clientId = $mode === 'live' ? env('PAYPAL_CLIENT_ID_LIVE') : env('PAYPAL_CLIENT_ID_SANDBOX', env('PAYPAL_CLIENT_ID_LIVE'));
        $secret   = $mode === 'live' ? env('PAYPAL_SECRET_LIVE') : env('PAYPAL_SECRET_SANDBOX', env('PAYPAL_SECRET_LIVE'));
        $baseUrl  = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        if (!$clientId || !$secret) {
            Log::error('❌ Credenciales de PayPal incompletas en .env');
            return ['ok' => false, 'message' => 'Configuración de PayPal incompleta. Intenta más tarde.'];
        }

        $tokenResponse = Http::withBasicAuth($clientId, $secret)->asForm()->post("$baseUrl/v1/oauth2/token", ['grant_type' => 'client_credentials']);
        $accessToken = $tokenResponse['access_token'] ?? null;

        if (!$accessToken) {
            Log::error('❌ PayPal sin access_token en respuesta OAuth', ['json' => $tokenResponse->json()]);
            return ['ok' => false, 'message' => 'No se pudo obtener autorización de PayPal.'];
        }

        $orderResponse = Http::withToken($accessToken)->get("$baseUrl/v2/checkout/orders/$paypalOrderId");

        if (!$orderResponse->ok()) {
            return ['ok' => false, 'message' => 'No se pudo validar la orden de pago con PayPal.'];
        }

        $orderData = $orderResponse->json();
        if (($orderData['status'] ?? '') !== 'COMPLETED') {
            return ['ok' => false, 'message' => 'El pago aún no está completado en PayPal.'];
        }

        $amountValue = $orderData['purchase_units'][0]['amount']['value'] ?? null;
        $currencyCode = $orderData['purchase_units'][0]['amount']['currency_code'] ?? null;
        $formattedTotal = number_format($totalEsperado, 2, '.', '');

        if ($currencyCode !== 'MXN' || $amountValue != $formattedTotal) {
            Log::warning('⚠️ Desajuste PayPal', ['paypal' => $amountValue, 'esperado' => $formattedTotal]);
            return ['ok' => false, 'message' => 'El monto del pago no coincide con la reservación.'];
        }

        return ['ok' => true];
    }

    /**
     * Helper para folio único
     */
    private function generarFolioReservacionUnico(int $maxIntentos = 20): string
    {
        for ($i = 0; $i < $maxIntentos; $i++) {
            $letra1 = chr(random_int(65, 90));
            $num3   = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $letra2 = chr(random_int(65, 90));
            $num1   = (string) random_int(0, 9);
            $folio  = "MX-{$letra1}{$num3}{$letra2}{$num1}";

            if (!DB::table('reservaciones')->where('codigo', $folio)->exists()) {
                return $folio;
            }
        }
        throw new \RuntimeException('No se pudo generar un folio único para la reservación.');
    }
}
