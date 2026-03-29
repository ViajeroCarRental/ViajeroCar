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

            // 2️⃣ Generar folio (MX-E480A1)
            $codigo = $this->generarFolioReservacionUnico();

            // 3️⃣ Calcular totales base usando la CATEGORÍA (no el vehículo)
            $categoria = DB::table('categorias_carros')
                ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
                ->where('id_categoria', $validated['categoria_id'])
                ->first();

            // ✅ CÁLCULO DE TIEMPO EXACTO
            $fechaInicio = Carbon::parse($validated['pickup_date'] . ' ' . $validated['pickup_time']);
            $fechaFin    = Carbon::parse($validated['dropoff_date'] . ' ' . $validated['dropoff_time']);

            $horasTotales = $fechaInicio->diffInHours($fechaFin);
            $diasBase = intdiv($horasTotales, 24);
            $horasExtra = $horasTotales % 24;

            // ✅ 1 hora de tolerancia
            if ($horasExtra > 1) {
                $dias = $diasBase + 1;
            } else {
                $dias = max(1, $diasBase);
            }

            $precioDia = $categoria ? (float)$categoria->precio_dia : 0.0;

            if ($precioDia > 0) {
                $precioDia = round($precioDia * 1.15);
            }

            $subtotalBase = $precioDia * $dias;

            // 3.1️⃣ ADDONS: parsear cadena "id:cant,id2:cant2"
            $addonsRaw = $validated['addons'] ?? '';
            $addonsMap = [];   // [id_servicio => cantidad]

            if (!empty($addonsRaw)) {
                foreach (explode(',', $addonsRaw) as $pair) {
                    $pair = trim($pair);
                    if ($pair === '') {
                        continue;
                    }

                    $matches = [];
                    if (!preg_match('/^(\d+)\s*:\s*(\d+)$/', $pair, $matches)) {
                        continue;
                    }

                    $id  = (int)$matches[1];
                    $qty = (int)$matches[2];

                    if ($id <= 0 || $qty <= 0) {
                        continue;
                    }

                    // Si se repite el mismo id, acumulamos
                    $addonsMap[$id] = ($addonsMap[$id] ?? 0) + $qty;
                }
            }

            // 3.2️⃣ Calcular subtotal de servicios adicionales
            $extrasSubtotal = 0.0;
            $serviciosAdd   = []; // lo reutilizamos después para insertar en reservacion_servicio

            $capacidadTanque = (float) (
                DB::table('vehiculos')
                ->where('id_categoria', $validated['categoria_id'])
                ->where('id_estatus', 1)
                ->max('capacidad_tanque') ?? 0
            );

            if (!empty($addonsMap)) {
                $serviciosAdd = DB::table('servicios')
                    ->whereIn('id_servicio', array_keys($addonsMap))
                    ->get();

                foreach ($serviciosAdd as $srv) {
                    $idServicio = (int)$srv->id_servicio;

                    // ✅ SALTAMOS EL DROPOFF PARA EVITAR DUPLICADOS (ERROR 1062)
                    if ($idServicio === 11) {
                        continue;
                    }

                    $cantidad   = $addonsMap[$idServicio] ?? 0;
                    if ($cantidad <= 0) {
                        continue;
                    }

                    $precioBase = (float)$srv->precio;
                    $tipoCobro  = strtolower((string)$srv->tipo_cobro);

                    // Gasolina prepago -> precio por litro * litros del tanque
                    if ($idServicio === 1) {
                        $lineTotal = $precioBase * $capacidadTanque;
                    } elseif ($tipoCobro === 'por_tanque') {
                        $lineTotal = $precioBase * $capacidadTanque * $cantidad;
                    } elseif ($tipoCobro === 'por_evento') {
                        $lineTotal = $precioBase * $cantidad;
                    } else {
                        $lineTotal = $precioBase * $cantidad * $dias;
                    }

                    $extrasSubtotal += $lineTotal;
                }
            }

            // ======================================================
            // ✅ CALCULAR DROP OFF DINÁMICO
            // ======================================================
            $montoDropoff = 0;
            if (!empty($validated['pickup_sucursal_id']) && !empty($validated['dropoff_sucursal_id']) && $validated['pickup_sucursal_id'] != $validated['dropoff_sucursal_id']) {

                $nombreDestino = DB::table('sucursales')
                    ->where('id_sucursal', $validated['dropoff_sucursal_id'])
                    ->value('nombre');

                if ($nombreDestino) {
                    $km = DB::table('ubicaciones_servicio')
                        ->where('destino', $nombreDestino)
                        ->where('activo', true)
                        ->value('km') ?? 0;

                    $costoKm = DB::table('categoria_costo_km')
                        ->where('id_categoria', $validated['categoria_id'])
                        ->where('activo', true)
                        ->value('costo_km') ?? 0;

                    $montoDropoff = (float)$km * (float)$costoKm;
                    $extrasSubtotal += $montoDropoff; // 👈 Aquí se suma al subtotal de la tabla
                }
            }

            // 3.3️⃣ Subtotal final, IVA y total (base + extras)
            $subtotal  = $subtotalBase + $extrasSubtotal;
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

            // 5️⃣ Insertar reservación (ya con totales incluyendo extras)
            $id = DB::table('reservaciones')->insertGetId([
                'id_usuario'       => null,
                'id_vehiculo'      => null, // 👉 se asigna después en el contrato
                'id_categoria'     => $validated['categoria_id'],
                'tarifa_base'      => $precioDia,
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

            // 5.1️⃣ Insertar servicios adicionales en reservacion_servicio
            if (!empty($serviciosAdd)) {
                foreach ($serviciosAdd as $srv) {
                    $idServicio = (int)$srv->id_servicio;

                    // ✅ EVITAR INSERTAR DROP OFF AQUÍ
                    if ($idServicio === 11) {
                        continue;
                    }

                    $cantidad   = $addonsMap[$idServicio] ?? 0;
                    if ($cantidad <= 0) {
                        continue;
                    }

                    $precioBase = (float)$srv->precio;
                    $tipoCobro  = strtolower((string)$srv->tipo_cobro);

                    // Gasolina prepago: guardar litros como cantidad y precio por litro como precio_unitario
                    if ($idServicio === 1) {
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => $idServicio,
                            'id_contrato'     => null,
                            'cantidad'        => max(1, (int) round($capacidadTanque)),
                            'precio_unitario' => $precioBase,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                        continue;
                    }

                    if ($tipoCobro === 'por_tanque') {
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => $idServicio,
                            'id_contrato'     => null,
                            'cantidad'        => max(1, (int) round($capacidadTanque)) * $cantidad,
                            'precio_unitario' => $precioBase,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                        continue;
                    }

                    if ($tipoCobro === 'por_evento') {
                        $precioUnitario = $precioBase;
                    } else {
                        $precioUnitario = $precioBase * $dias;
                    }

                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $id,
                        'id_servicio'     => $idServicio,
                        'id_contrato'     => null,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }

            // ✅ GUARDAR EL DROP-OFF EN EL DESGLOSE DE MANERA ÚNICA
            if ($montoDropoff > 0) {
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $id,
                    'id_servicio'     => 11,
                    'id_contrato'     => null,
                    'cantidad'        => 1,
                    'precio_unitario' => $montoDropoff,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // 6️⃣ Enviar correo con plantilla (PAGO EN MOSTRADOR)
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            // ===============================
            // ✅ Ficha "Tu Auto" (como en admin)
            // ===============================
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

            // Nombre en singular para subtítulo
            $nombreCat = trim((string)($categoria->nombre ?? ''));
            $singular = $nombreCat;
            if (mb_substr($singular, -1) === 's') {
                $singular = mb_substr($singular, 0, mb_strlen($singular) - 1);
            }
            $singular = mb_strtoupper($singular);

            // Título: descripción
            $tituloAuto = trim((string)($categoria->descripcion ?? 'Auto o similar'));

            // Subtítulo: "GRANDE | CATEGORÍA E"
            $subtituloAuto = $singular . " | CATEGORÍA " . ($codigoCat ?: '-');

            $tuAuto = [
                'titulo'     => $tituloAuto,
                'subtitulo'  => $subtituloAuto,
                'pax'        => (int)$cap['pax'],
                'small'      => (int)$cap['small'],
                'big'        => (int)$cap['big'],
                'transmision' => 'Transmisión manual o automática',
                'tech'       => 'Apple CarPlay | Android Auto',
                'incluye'    => 'KM ilimitados | Reelevo de Responsabilidad (LI)',
            ];

            // ===============================
            // ✅ Traer SERVICIOS (extras) ligados a la reservación
            // ===============================
            $extrasReserva = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 's.id_servicio', '=', 'rs.id_servicio')
                ->where('rs.id_reservacion', $id)
                ->select(
                    's.id_servicio',
                    's.nombre',
                    's.descripcion',
                    'rs.cantidad',
                    'rs.precio_unitario',
                    DB::raw('(rs.cantidad * rs.precio_unitario) as total')
                )
                ->get();

            // ===============================
            // ✅ Traer SUCURSAL + CIUDAD (retiro / entrega) para el correo
            // ===============================
            $retiroInfo = DB::table('sucursales as s')
                ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
                ->where('s.id_sucursal', $reservacion->sucursal_retiro)
                ->select('s.nombre as sucursal', 'c.nombre as ciudad')
                ->first();

            $entregaInfo = DB::table('sucursales as s')
                ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
                ->where('s.id_sucursal', $reservacion->sucursal_entrega)
                ->select('s.nombre as sucursal', 'c.nombre as ciudad')
                ->first();

            $lugarRetiro  = $retiroInfo ? ($retiroInfo->ciudad . ' - ' . $retiroInfo->sucursal) : '-';
            $lugarEntrega = $entregaInfo ? ($entregaInfo->ciudad . ' - ' . $entregaInfo->sucursal) : '-';

            // ===============================
            // ✅ Imagen por categoría (mismo mapeo que admin)
            // ===============================
            $catImages = [
                1  => 'img/aveo.png',
                2  => 'img/virtus.png',
                3  => 'img/jetta.png',
                4  => 'img/camry.png',
                5  => 'img/renegade.png',
                6  => 'img/taos.png',
                7  => 'img/avanza.png',
                8  => 'img/Odyssey.png',
                9  => 'img/Hiace.png',
                10 => 'img/Frontier.png',
                11 => 'img/Tacoma.png',
            ];

            $catId  = (int)($categoria->id_categoria ?? 0);
            $baseUrl = rtrim(config('app.url'), '/');
            $imgPath = $catImages[$catId] ?? 'img/categorias/placeholder.png';
            $imgCategoria = $baseUrl . '/' . ltrim($imgPath, '/');

            // ===============================
            // ✅ Total "Opciones de renta" (solo servicios, SIN seguros)
            // ===============================
            $extrasServiciosTotal = 0;
            if (!empty($extrasReserva)) {
                $extrasServiciosTotal = (float) $extrasReserva->sum('total');
            }

            $opcionesRentaTotal = round($extrasServiciosTotal, 2);

            if (!empty($reservacion->email_cliente)) {
                Mail::to($reservacion->email_cliente)
                    ->cc(env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com'))
                    ->send(new ReservacionUsuarioMail(
                        $reservacion,
                        'mostrador',
                        $categoria,
                        $extrasReserva,
                        $lugarRetiro,
                        $lugarEntrega,
                        $imgCategoria,
                        $opcionesRentaTotal,
                        $tuAuto
                    ));
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

            // 2️⃣ Folio (MX-E480A1)
            $codigo = $this->generarFolioReservacionUnico();

            // 3️⃣ Totales base (categoría)
            $categoria = DB::table('categorias_carros')
                ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
                ->where('id_categoria', $validated['categoria_id'])
                ->first();

            $fechaInicio = Carbon::parse($validated['pickup_date']);
            $fechaFin    = Carbon::parse($validated['dropoff_date']);
            $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

            $precioDia    = $categoria ? (float)$categoria->precio_dia : 0.0;
            $subtotalBase = $precioDia * $dias;

            // 3.1️⃣ ADDONS: parsear cadena "id:cant,id2:cant2"
            $addonsRaw = $validated['addons'] ?? '';
            $addonsMap = [];   // [id_servicio => cantidad]

            if (!empty($addonsRaw)) {
                foreach (explode(',', $addonsRaw) as $pair) {
                    $pair = trim($pair);
                    if ($pair === '') {
                        continue;
                    }

                    $matches = [];
                    if (!preg_match('/^(\d+)\s*:\s*(\d+)$/', $pair, $matches)) {
                        continue;
                    }

                    $id  = (int)$matches[1];
                    $qty = (int)$matches[2];

                    if ($id <= 0 || $qty <= 0) {
                        continue;
                    }

                    $addonsMap[$id] = ($addonsMap[$id] ?? 0) + $qty;
                }
            }

            // 3.2️⃣ Subtotal de servicios adicionales
            $extrasSubtotal = 0.0;
            $serviciosAdd   = [];

            $capacidadTanque = (float) (
                DB::table('vehiculos')
                ->where('id_categoria', $validated['categoria_id'])
                ->where('id_estatus', 1)
                ->max('capacidad_tanque') ?? 0
            );

            if (!empty($addonsMap)) {
                $serviciosAdd = DB::table('servicios')
                    ->whereIn('id_servicio', array_keys($addonsMap))
                    ->get();

                foreach ($serviciosAdd as $srv) {
                    $idServicio = (int)$srv->id_servicio;
                    $cantidad   = $addonsMap[$idServicio] ?? 0;
                    if ($cantidad <= 0) {
                        continue;
                    }

                    $precioBase = (float)$srv->precio;
                    $tipoCobro  = strtolower((string)$srv->tipo_cobro);

                    if ($idServicio === 1) {
                        $lineTotal = $precioBase * $capacidadTanque;
                    } elseif ($tipoCobro === 'por_tanque') {
                        $lineTotal = $precioBase * $capacidadTanque * $cantidad;
                    } elseif ($tipoCobro === 'por_evento') {
                        $lineTotal = $precioBase * $cantidad;
                    } else {
                        $lineTotal = $precioBase * $cantidad * $dias;
                    }

                    $extrasSubtotal += $lineTotal;
                }
            }

            // 3.3️⃣ Subtotal final, IVA y total
            $subtotal  = $subtotalBase + $extrasSubtotal;
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

            // 4.3 Validar monto y moneda (incluyendo adicionales)
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
                'id_categoria'     => $validated['categoria_id'],
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

            // 6.1️⃣ Insertar servicios adicionales en reservacion_servicio
            if (!empty($serviciosAdd)) {
                foreach ($serviciosAdd as $srv) {
                    $idServicio = (int)$srv->id_servicio;
                    $cantidad   = $addonsMap[$idServicio] ?? 0;
                    if ($cantidad <= 0) {
                        continue;
                    }

                    $precioBase = (float)$srv->precio;
                    $tipoCobro  = strtolower((string)$srv->tipo_cobro);

                    if ($idServicio === 1) {
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => $idServicio,
                            'id_contrato'     => null,
                            'cantidad'        => max(1, (int) round($capacidadTanque)),
                            'precio_unitario' => $precioBase,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                        continue;
                    }

                    if ($tipoCobro === 'por_tanque') {
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => $idServicio,
                            'id_contrato'     => null,
                            'cantidad'        => max(1, (int) round($capacidadTanque)) * $cantidad,
                            'precio_unitario' => $precioBase,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                        continue;
                    }

                    if ($tipoCobro === 'por_evento') {
                        $precioUnitario = $precioBase;
                    } else {
                        $precioUnitario = $precioBase * $dias;
                    }

                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $id,
                        'id_servicio'     => $idServicio,
                        'id_contrato'     => null,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }

            // 7️⃣ Enviar correo con plantilla (PAGO EN LÍNEA)
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            // ===============================
            // ✅ Ficha "Tu Auto" (misma lógica que en mostrador)
            // ===============================
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

            $nombreCat = trim((string)($categoria->nombre ?? ''));
            $singular = $nombreCat;
            if (mb_substr($singular, -1) === 's') {
                $singular = mb_substr($singular, 0, mb_strlen($singular) - 1);
            }
            $singular = mb_strtoupper($singular);

            $tituloAuto = trim((string)($categoria->descripcion ?? 'Auto o similar'));
            $subtituloAuto = $singular . " | CATEGORÍA " . ($codigoCat ?: '-');

            $tuAuto = [
                'titulo'     => $tituloAuto,
                'subtitulo'  => $subtituloAuto,
                'pax'        => (int)$cap['pax'],
                'small'      => (int)$cap['small'],
                'big'        => (int)$cap['big'],
                'transmision' => 'Transmisión manual o automática',
                'tech'       => 'Apple CarPlay | Android Auto',
                'incluye'    => 'KM ilimitados | Reelevo de Responsabilidad (LI)',
            ];

            // ===============================
            // ✅ Traer SERVICIOS (extras) para el correo
            // ===============================
            $extrasReserva = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 's.id_servicio', '=', 'rs.id_servicio')
                ->where('rs.id_reservacion', $id)
                ->select(
                    's.id_servicio',
                    's.nombre',
                    's.descripcion',
                    'rs.cantidad',
                    'rs.precio_unitario',
                    DB::raw('(rs.cantidad * rs.precio_unitario) as total')
                )
                ->get();

            // ===============================
            // ✅ Sucursales / ciudades
            // ===============================
            $retiroInfo = DB::table('sucursales as s')
                ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
                ->where('s.id_sucursal', $reservacion->sucursal_retiro)
                ->select('s.nombre as sucursal', 'c.nombre as ciudad')
                ->first();

            $entregaInfo = DB::table('sucursales as s')
                ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
                ->where('s.id_sucursal', $reservacion->sucursal_entrega)
                ->select('s.nombre as sucursal', 'c.nombre as ciudad')
                ->first();

            $lugarRetiro  = $retiroInfo ? ($retiroInfo->ciudad . ' - ' . $retiroInfo->sucursal) : '-';
            $lugarEntrega = $entregaInfo ? ($entregaInfo->ciudad . ' - ' . $entregaInfo->sucursal) : '-';

            // ===============================
            // ✅ Imagen por categoría (mismo mapeo)
            // ===============================
            $catImages = [
                1  => 'img/aveo.png',
                2  => 'img/virtus.png',
                3  => 'img/jetta.png',
                4  => 'img/camry.png',
                5  => 'img/renegade.png',
                6  => 'img/taos.png',
                7  => 'img/avanza.png',
                8  => 'img/Odyssey.png',
                9  => 'img/Hiace.png',
                10 => 'img/Frontier.png',
                11 => 'img/Tacoma.png',
            ];

            $catId  = (int)($categoria->id_categoria ?? 0);
            $baseUrl = rtrim(config('app.url'), '/');
            $imgPath = $catImages[$catId] ?? 'img/categorias/placeholder.png';
            $imgCategoria = $baseUrl . '/' . ltrim($imgPath, '/');

            // ===============================
            // ✅ Total "Opciones de renta" (solo servicios)
            // ===============================
            $extrasServiciosTotal = 0;
            if (!empty($extrasReserva)) {
                $extrasServiciosTotal = (float) $extrasReserva->sum('total');
            }

            $opcionesRentaTotal = round($extrasServiciosTotal, 2);

            if (!empty($reservacion->email_cliente)) {
                Mail::to($reservacion->email_cliente)
                    ->cc(env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocar-rental.com'))
                    ->send(new ReservacionUsuarioMail(
                        $reservacion,
                        'en_linea',
                        $categoria,
                        $extrasReserva,
                        $lugarRetiro,
                        $lugarEntrega,
                        $imgCategoria,
                        $opcionesRentaTotal,
                        $tuAuto
                    ));
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

    // ======================================================
    // ✅ Generación de folio: MX- + L + NNN + L + N
    //    Ej: MX-E480A1 (9 caracteres contando el guion)
    // ======================================================
    private function generarFolioReservacion(): string
    {
        $letra1 = chr(random_int(65, 90)); // A-Z
        $num3   = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT); // 000-999
        $letra2 = chr(random_int(65, 90)); // A-Z
        $num1   = (string) random_int(0, 9); // 0-9

        return "MX-{$letra1}{$num3}{$letra2}{$num1}";
    }

    /**
     * Genera un folio y asegura que NO exista en la BD.
     * (Reintenta varias veces para evitar colisiones).
     */
    private function generarFolioReservacionUnico(int $maxIntentos = 20): string
    {
        for ($i = 0; $i < $maxIntentos; $i++) {
            $folio = $this->generarFolioReservacion();

            $existe = DB::table('reservaciones')
                ->where('codigo', $folio)
                ->exists();

            if (!$existe) {
                return $folio;
            }
        }

        // Si llega aquí, es extremadamente raro, pero mejor fallar con mensaje claro
        throw new \RuntimeException('No se pudo generar un folio único para la reservación.');
    }
}
