<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Tolerancia en MXN para diferencias de redondeo entre lo que cobra PayPal
     * y lo que recalcula el backend. Si difieren menos de esto, se acepta.
     */
    private const TOLERANCIA_PAYPAL_MXN = 1.00;

    /**
     * Multiplicador para el precio diario cuando el cliente paga en mostrador.
     * Debe coincidir EXACTAMENTE con el multiplicador usado en
     * ReservacionesController para mostrar los precios al cliente en Step 2.
     */
    private const MOSTRADOR_MULTIPLIER = 1.25;

    /**
     * Reserva creada por el Agente IA (WhatsApp / voz).
     * Reutiliza exactamente la misma lógica que reservar() en mostrador:
     * cálculo, guardado y correo. La diferencia es que se protege con un
     * token secreto (header X-Agente-Token) en vez de CSRF, porque la
     * llamada viene de un sistema externo (la API de Python), no de un navegador.
     */
    public function reservarAgente(Request $request)
    {
        // 1. Validar el token secreto del agente
        $tokenRecibido = $request->header('X-Agente-Token');
        $tokenEsperado = env('AGENTE_API_TOKEN');

        if (!$tokenEsperado || $tokenRecibido !== $tokenEsperado) {
            return response()->json([
                'ok'      => false,
                'message' => 'No autorizado.',
            ], 401);
        }

       // 2. Validar los datos de entrada (mismas reglas que el formulario web)
        $validated = $request->validate([
            'categoria_id'        => 'required|integer|exists:categorias_carros,id_categoria',
            'pickup_date'         => 'required|date',
            'pickup_time'         => 'required',
            'dropoff_date'        => 'required|date',
            'dropoff_time'        => 'required',
            'pickup_sucursal_id'  => 'required|integer',
            'dropoff_sucursal_id' => 'required|integer',
            'nombre'              => 'required|string|max:120',
            'email'               => 'required|string|max:120',
            'telefono'            => 'required|string|max:40',
            'vuelo'               => 'nullable|string|max:40',
            'addons'              => 'nullable|string',
            'idioma'              => 'nullable|string|in:es,en',
            'fecha_nacimiento'    => 'nullable|date',
        ]);

        // Aplicar el idioma del cliente (es/en) para que el correo salga en su idioma.
        // Si no se manda, por defecto español.
        app()->setLocale($validated['idioma'] ?? 'es');

        try {
            // 3. Reutilizar EXACTAMENTE la misma lógica que el mostrador
            $data = $this->procesarCalculosYResumen($validated, 'mostrador');

            $codigo = $this->generarFolioReservacionUnico();

            $id = DB::transaction(function () use ($validated, $data, $codigo) {
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
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $this->guardarServiciosReservacion($id, $data['serviciosAInsertar']);

                return $id;
            });

            // 4. Enviar el correo (mismo que el mostrador: cliente + cc reservaciones)
            $this->enviarCorreoConfirmacion($id, 'mostrador', $data);

            return response()->json([
                'ok'        => true,
                'folio'     => $codigo,
                'id'        => $id,
                'subtotal'  => $data['subtotal'],
                'impuestos' => $data['impuestos'],
                'total'     => $data['total'],
                'estado'    => 'pendiente_pago',
                'message'   => 'Reservación creada con éxito por el agente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[ERROR] Falla creando reservación (agente): ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Error interno al crear la reservación',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guarda una reservación real (pago en mostrador)
     */
    public function reservar(StoreReservacionRequest $request)
    {
        try {
            $validated = $request->validated();

            // Calcular subtotales, servicios e info de la categoría
            $data = $this->procesarCalculosYResumen($validated, 'mostrador');

            // Generar folio único
            $codigo = $this->generarFolioReservacionUnico();

            // Transacción atómica: reserva + servicios juntos o nada
            $id = DB::transaction(function () use ($validated, $data, $codigo) {
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
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $this->guardarServiciosReservacion($id, $data['serviciosAInsertar']);

                return $id;
            });

            // Correo fuera de la transacción (si falla, la reserva queda guardada)
            $this->enviarCorreoConfirmacion($id, 'mostrador', $data);

            return response()->json([
                'ok'        => true,
                'folio'     => $codigo,
                'id'        => $id,
                'subtotal'  => $data['subtotal'],
                'impuestos' => $data['impuestos'],
                'total'     => $data['total'],
                'estado'    => 'pendiente_pago',
                'message'   => 'Reservación creada con éxito.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[ERROR] Falla creando reservación (mostrador): ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Error interno al crear la reservación',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validar pago en PayPal y guardar reservación en línea.
     *
     * FILOSOFÍA: si PayPal ya cobró, NUNCA se rechaza el guardado.
     * Si hay desajustes menores, se loggean y se guarda con marca de revisión.
     *
     * IDEMPOTENCIA: si ya existe una reserva con el mismo paypal_order_id
     * (caso de reintento desde el JS), devuelve la existente sin duplicar.
     */
    public function reservarLinea(StoreReservacionLineaRequest $request)
    {
        try {
            $validated = $request->validated();

            // --- IDEMPOTENCIA: detectar reintento desde el frontend ---
            // El JS tiene retry con backoff: si el primer POST se cae en
            // el camino pero el cobro ya pasó, el JS reintenta hasta 3 veces.
            // Aquí evitamos crear reservas duplicadas con el mismo order_id.
            $reservaExistente = DB::table('reservaciones')
                ->where('paypal_order_id', $validated['paypal_order_id'])
                ->first();

            if ($reservaExistente) {
                Log::info('[INFO] Reintento detectado de reserva PayPal ya registrada', [
                    'paypal_order_id' => $validated['paypal_order_id'],
                    'id_reservacion'  => $reservaExistente->id_reservacion,
                    'folio'           => $reservaExistente->codigo,
                ]);

                return response()->json([
                    'ok'               => true,
                    'folio'            => $reservaExistente->codigo,
                    'id'               => $reservaExistente->id_reservacion,
                    'subtotal'         => (float) $reservaExistente->subtotal,
                    'impuestos'        => (float) $reservaExistente->impuestos,
                    'total'            => (float) $reservaExistente->total,
                    'estado'           => $reservaExistente->estado,
                    'correo_enviado'   => false,
                    'requiere_revision' => false,
                    'reuse'            => true,
                    'message'          => 'Reserva ya registrada previamente.',
                ]);
            }

            // Total que cobró PayPal según el frontend (referencia)
            $totalLocal = isset($validated['total_local']) ? (float) $validated['total_local'] : null;

            // Cálculos del backend (única fuente confiable)
            $data = $this->procesarCalculosYResumen($validated, 'linea');

            // Validar pago con PayPal (con tolerancia de centavos)
            $paypalValidado = $this->validarPagoPayPal(
                $validated['paypal_order_id'],
                $data['total'],
                $totalLocal
            );

            // Si PayPal NO cobró (o no existe la orden), aquí SÍ rechazamos
            if (!$paypalValidado['ok'] && !$paypalValidado['cobro_realizado']) {
                Log::warning('[WARN] Pago NO cobrado por PayPal, rechazando reserva', [
                    'paypal_order_id' => $validated['paypal_order_id'],
                    'detalle'         => $paypalValidado,
                ]);
                return response()->json([
                    'ok'      => false,
                    'message' => $paypalValidado['message'] ?? 'El pago no fue completado en PayPal.',
                ], 422);
            }

            // Si PayPal sí cobró pero hay desajuste, GUARDAMOS igual y loggeamos
            $requiereRevision = !$paypalValidado['ok'] && $paypalValidado['cobro_realizado'];

            if ($requiereRevision) {
                Log::warning('[WARN] Reserva guardada con desajuste de monto - REQUIERE REVISIÓN', [
                    'paypal_order_id' => $validated['paypal_order_id'],
                    'monto_paypal'    => $paypalValidado['monto_paypal'] ?? null,
                    'monto_backend'   => $data['total'],
                    'monto_frontend'  => $totalLocal,
                    'cliente_email'   => $validated['email'] ?? null,
                    'cliente_nombre'  => $validated['nombre'] ?? null,
                    'detalle'         => $paypalValidado,
                ]);
            }

            // Generar folio único
            $codigo = $this->generarFolioReservacionUnico();

            // El total a guardar es el que PayPal realmente cobró
            $totalAGuardar = $paypalValidado['monto_paypal'] ?? $data['total'];

            // Transacción atómica: reserva + servicios + pago juntos o nada
            $id = DB::transaction(function () use ($validated, $data, $codigo, $totalAGuardar, $paypalValidado) {
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
                    'total'            => $totalAGuardar,
                    'moneda'           => 'MXN',
                    'no_vuelo'         => $validated['vuelo'] ?? null,
                    'codigo'           => $codigo,
                    'nombre_cliente'   => $validated['nombre'] ?? null,
                    'email_cliente'    => $validated['email'] ?? null,
                    'telefono_cliente' => $validated['telefono'] ?? null,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                    'paypal_order_id'  => $validated['paypal_order_id'],
                    'status_pago'      => 'Pagado',
                    'metodo_pago'      => 'en_linea',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $this->guardarServiciosReservacion($id, $data['serviciosAInsertar']);

                // Registrar el pago en la tabla `pagos`
                $this->guardarPagoPayPal($id, $validated['paypal_order_id'], $totalAGuardar, $paypalValidado);

                return $id;
            });

            if ($requiereRevision) {
                Log::warning('[WARN] Reserva creada que requiere revisión manual', [
                    'id_reservacion' => $id,
                    'folio'          => $codigo,
                    'paypal_order'   => $validated['paypal_order_id'],
                ]);
            }

            // Correo fuera de la transacción
            $correoEnviado = $this->enviarCorreoConfirmacion($id, 'en_linea', $data);

            return response()->json([
                'ok'                => true,
                'folio'             => $codigo,
                'id'                => $id,
                'subtotal'          => $data['subtotal'],
                'impuestos'         => $data['impuestos'],
                'total'             => $totalAGuardar,
                'estado'            => 'confirmada',
                'correo_enviado'    => $correoEnviado,
                'requiere_revision' => $requiereRevision,
                'message'           => 'Pago validado con PayPal y reserva confirmada correctamente.',
            ]);
        } catch (\Throwable $e) {
            // LOG CRÍTICO: si esto pasa con PayPal ya cobrado, hay un cobro huérfano
            Log::critical('[CRITICAL] Error en reservarLinea (posible cobro huérfano): ' . $e->getMessage(), [
                'trace'           => $e->getTraceAsString(),
                'paypal_order_id' => $request->input('paypal_order_id'),
                'cliente_email'   => $request->input('email'),
                'cliente_nombre'  => $request->input('nombre'),
                'total_local'     => $request->input('total_local'),
                'payload'         => $request->all(),
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Error interno al procesar la reserva en línea. Tu pago fue recibido; nos pondremos en contacto contigo a la brevedad.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Realiza todos los cálculos (días, addons, dropoff, impuestos)
     * y genera la estructura de datos que comparten mostrador, PayPal y los correos.
     */
    private function procesarCalculosYResumen(array $validated, string $tipoPlan): array
    {
        // --- Días de Renta y Tolerancia ---
        $fechaInicio = Carbon::parse($validated['pickup_date'] . ' ' . $validated['pickup_time']);
        $fechaFin    = Carbon::parse($validated['dropoff_date'] . ' ' . $validated['dropoff_time']);

        $horasTotales = $fechaInicio->diffInHours($fechaFin);
        $diasBase     = intdiv($horasTotales, 24);
        $horasExtra   = $horasTotales % 24;
        $dias         = ($horasExtra > 1) ? $diasBase + 1 : max(1, $diasBase);

        // --- Tarifa Base ---
        $categoria = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
            ->where('id_categoria', $validated['categoria_id'])
            ->first();

        $precioDia = $categoria ? (float) $categoria->precio_dia : 0.0;

        // Si el plan es mostrador, aplicar multiplicador (1.25)
        // Debe coincidir con ReservacionesController para mantener consistencia
        // entre lo que ve el cliente en Step 2 y lo que se guarda en BD.
        if ($tipoPlan === 'mostrador' && $precioDia > 0) {
            $precioDia = round($precioDia * self::MOSTRADOR_MULTIPLIER);
        }

        $subtotalBase = $precioDia * $dias;

        // --- Servicios Extras (Addons) ---
        $addonsMap = [];
        if (!empty($validated['addons'])) {
            foreach (explode(',', $validated['addons']) as $pair) {
                $pair = trim($pair);
                if (preg_match('/^(\d+)\s*:\s*(\d+)$/', $pair, $matches)
                    && (int) $matches[1] > 0 && (int) $matches[2] > 0) {
                    $id = (int) $matches[1];
                    $addonsMap[$id] = ($addonsMap[$id] ?? 0) + (int) $matches[2];
                }
            }
        }

        $capacidadTanque = (float) (DB::table('vehiculos')
            ->where('id_categoria', $validated['categoria_id'])
            ->where('id_estatus', 1)
            ->max('capacidad_tanque') ?? 50);

        $extrasSubtotal     = 0.0;
        $serviciosAInsertar = [];
        $extrasParaCorreo   = collect();

        if (!empty($addonsMap)) {
            $serviciosDB = DB::table('servicios')
                ->whereIn('id_servicio', array_keys($addonsMap))
                ->get();

            foreach ($serviciosDB as $srv) {
                // Saltar dropoff si viene inyectado por error desde frontend
                if ($srv->id_servicio == 11) continue;

                $cantidad         = $addonsMap[$srv->id_servicio] ?? 0;
                $precioBase       = (float) $srv->precio;
                $tipoCobro        = strtolower((string) $srv->tipo_cobro);
                $cantParaDB       = $cantidad;
                $precioUnitarioDB = $precioBase;
                $lineTotal        = 0;

                if ($srv->id_servicio == 1) {
                    // Gasolina prepago: cobro por tanque
                    $lineTotal  = $precioBase * $capacidadTanque;
                    $cantParaDB = max(1, (int) round($capacidadTanque));
                } elseif ($tipoCobro === 'por_tanque') {
                    $lineTotal  = $precioBase * $capacidadTanque * $cantidad;
                    $cantParaDB = max(1, (int) round($capacidadTanque)) * $cantidad;
                } elseif ($tipoCobro === 'por_evento') {
                    $lineTotal = $precioBase * $cantidad;
                } else {
                    // Por día (default)
                    $lineTotal        = $precioBase * $cantidad * $dias;
                    $precioUnitarioDB = $precioBase * $dias;
                }

                $extrasSubtotal += $lineTotal;

                $serviciosAInsertar[] = [
                    'id_servicio'     => $srv->id_servicio,
                    'cantidad'        => $cantParaDB,
                    'precio_unitario' => $precioUnitarioDB,
                ];

                $srv->cantidad        = $cantParaDB;
                $srv->precio_unitario = $precioUnitarioDB;
                $srv->total           = $lineTotal;
                $extrasParaCorreo->push($srv);
            }
        }

        // --- Drop Off: ciudad de entrega + cargo dinámico ---
        // Resolver sucursales de pickup y dropoff en UNA sola query
        $sucursalesIds = array_filter([
            $validated['pickup_sucursal_id'] ?? null,
            $validated['dropoff_sucursal_id'] ?? null,
        ]);

        $sucursales = empty($sucursalesIds)
            ? collect()
            : DB::table('sucursales')
                ->select('id_sucursal', 'id_ciudad', 'nombre')
                ->whereIn('id_sucursal', $sucursalesIds)
                ->get()
                ->keyBy('id_sucursal');

        $sucPickup  = !empty($validated['pickup_sucursal_id'])
            ? $sucursales->get($validated['pickup_sucursal_id'])  : null;
        $sucDropoff = !empty($validated['dropoff_sucursal_id'])
            ? $sucursales->get($validated['dropoff_sucursal_id']) : null;

        $ciudadRetiro  = $sucPickup->id_ciudad  ?? 1;
        $ciudadEntrega = $sucDropoff->id_ciudad ?? $ciudadRetiro;

        // Detectar si hay drop off (sucursales distintas)
        $hayDropoff = (
            !empty($validated['pickup_sucursal_id'])
            && !empty($validated['dropoff_sucursal_id'])
            && $validated['pickup_sucursal_id'] != $validated['dropoff_sucursal_id']
        );

        if ($hayDropoff && $sucDropoff) {
            $km = DB::table('ubicaciones_servicio')
                ->where('destino', $sucDropoff->nombre)
                ->where('activo', true)
                ->value('km') ?? 0;

            $costoKm = DB::table('categoria_costo_km')
                ->where('id_categoria', $validated['categoria_id'])
                ->where('activo', true)
                ->value('costo_km') ?? 0;

            $montoDropoff = (float) $km * (float) $costoKm;

            if ($montoDropoff > 0) {
                $extrasSubtotal += $montoDropoff;
                $serviciosAInsertar[] = [
                    'id_servicio'     => 11,
                    'cantidad'        => 1,
                    'precio_unitario' => $montoDropoff,
                ];
                $extrasParaCorreo->push((object) [
                    'id_servicio'     => 11,
                    'nombre'          => 'Cargo por Devolución (Drop-off)',
                    'descripcion'     => 'Entrega en sucursal diferente',
                    'cantidad'        => 1,
                    'precio_unitario' => $montoDropoff,
                    'total'           => $montoDropoff,
                ]);
            }
        }

        // --- Gran Total ---
        $subtotal  = $subtotalBase + $extrasSubtotal;
        $impuestos = round($subtotal * 0.16, 2);
        $total     = $subtotal + $impuestos;

        // --- Estructurar Ficha "Tu Auto" ---
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

        $codigoCat = strtoupper(trim((string) ($categoria->codigo ?? '')));
        $cap = $predeterminados[$codigoCat] ?? ['pax' => 5, 'small' => 2, 'big' => 1];

        // La VAN (categoría 9) es la única manual
        $esManual = ((int) ($categoria->id_categoria ?? 0) === 9);

        $singular = rtrim(mb_strtoupper(trim((string) ($categoria->nombre ?? ''))), 'S');
        $tuAuto = [
            'titulo'      => trim((string) ($categoria->descripcion ?? 'Auto o similar')),
            'subtitulo'   => $singular . ' | CATEGORÍA ' . ($codigoCat ?: '-'),
            'pax'         => (int) $cap['pax'],
            'small'       => (int) $cap['small'],
            'big'         => (int) $cap['big'],
            'transmision' => $esManual ? 'Manual' : 'Automática',   // ← CAMBIO
            'tech'        => 'Apple CarPlay | Android Auto',
            'incluye'     => 'KM ilimitados | Relevo de Responsabilidad (LI)',
        ];

        // --- Imagen de la categoría (WebP) ---
        $catImages = [
            1  => 'img/aveo.png',
            2  => 'img/virtus.webp',
            3  => 'img/jetta.webp',
            4  => 'img/camry.webp',
            5  => 'img/renegade.webp',
            6  => 'img/taos.webp',
            7  => 'img/avanza.webp',
            8  => 'img/Odyssey.webp',
            9  => 'img/Hiace.webp',
            10 => 'img/Frontier.webp',
            11 => 'img/Tacoma.webp',
        ];
        $catId        = (int) ($categoria->id_categoria ?? 0);
        $imgCategoria = asset($catImages[$catId] ?? 'img/categorias/placeholder.webp');

        return [
            'categoria'          => $categoria,
            'precioDia'          => $precioDia,
            'ciudadRetiro'       => $ciudadRetiro,
            'ciudadEntrega'      => $ciudadEntrega,
            'subtotal'           => $subtotal,
            'impuestos'          => $impuestos,
            'total'              => $total,
            'serviciosAInsertar' => $serviciosAInsertar,
            'extrasParaCorreo'   => $extrasParaCorreo,
            'opcionesRentaTotal' => round($extrasSubtotal, 2),
            'tuAuto'             => $tuAuto,
            'imgCategoria'       => $imgCategoria,
        ];
    }

    /**
     * Inserta todos los servicios adicionales calculados.
     * Se llama DENTRO de una DB::transaction para garantizar atomicidad.
     */
    private function guardarServiciosReservacion(int $reservacionId, array $servicios): void
    {
        if (empty($servicios)) return;

        $now = now();
        $insertData = [];
        foreach ($servicios as $srv) {
            $insertData[] = [
                'id_reservacion'  => $reservacionId,
                'id_servicio'     => $srv['id_servicio'],
                'id_contrato'     => null,
                'cantidad'        => $srv['cantidad'],
                'precio_unitario' => $srv['precio_unitario'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        DB::table('reservacion_servicio')->insert($insertData);
    }

    /**
     * Registra el pago de PayPal en la tabla `pagos`.
     * Se llama DENTRO de la DB::transaction de reservarLinea().
     *
     * La columna `referencia_pasarela` es UNIQUE: si por alguna razón
     * llega un duplicado, la excepción revierte toda la transacción,
     * evitando reservas duplicadas.
     */
    private function guardarPagoPayPal(
        int $reservacionId,
        string $paypalOrderId,
        float $montoCobrado,
        array $paypalValidado
    ): void {
        // Si hubo desajuste de monto, se guarda como 'paid' pero el payload
        // conserva la evidencia para la revisión manual.
        $requiereRevision = !$paypalValidado['ok'] && $paypalValidado['cobro_realizado'];

        $capturedAt = !empty($paypalValidado['captured_at'])
            ? Carbon::parse($paypalValidado['captured_at'])->toDateTimeString()
            : now()->toDateTimeString();

        $payload = [
            'order_data'        => $paypalValidado['order_data']     ?? null,
            'monto_esperado'    => $paypalValidado['monto_esperado'] ?? null,
            'monto_frontend'    => $paypalValidado['monto_frontend'] ?? null,
            'diferencia'        => $paypalValidado['diferencia']     ?? 0,
            'requiere_revision' => $requiereRevision,
        ];

        DB::table('pagos')->insert([
            'id_reservacion'      => $reservacionId,
            'id_contrato'         => null,
            'origen_pago'         => 'reservacion',
            'comprobante'         => null,
            'pasarela'            => 'paypal',
            'referencia_pasarela' => $paypalOrderId,
            'estatus'             => 'paid',
            'metodo'              => 'en_linea',
            'tipo_pago'           => 'total',
            'monto'               => $montoCobrado,
            'moneda'              => $paypalValidado['moneda_paypal'] ?? 'MXN',
            'tasa_cambio'         => null,
            'payload_webhook'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'captured_at'         => $capturedAt,
            'referencia_externa'  => $paypalValidado['capture_id'] ?? null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        Log::info('[INFO] Pago registrado en tabla pagos', [
            'id_reservacion'      => $reservacionId,
            'referencia_pasarela' => $paypalOrderId,
            'monto'               => $montoCobrado,
            'requiere_revision'   => $requiereRevision,
        ]);
    }

    /**
     * Envía correo de confirmación.
     * Si el SMTP falla, NO rompe la reserva. Loggea y continúa.
     *
     * @return bool true si el correo se envió, false si hubo error
     */
    private function enviarCorreoConfirmacion(int $reservacionId, string $tipoPlanCorreo, array $data): bool
    {
        try {
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $reservacionId)
                ->first();

            if (!$reservacion || empty($reservacion->email_cliente)) {
                Log::info('[INFO] Sin correo del cliente, no se envía notificación', [
                    'id_reservacion' => $reservacionId,
                ]);
                return false;
            }

            $sucursalesIds = array_filter([
                $reservacion->sucursal_retiro,
                $reservacion->sucursal_entrega,
            ]);

            $nombresSucursales = DB::table('sucursales as s')
                ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
                ->select('s.id_sucursal', 's.nombre as nombre_sucursal', 'c.nombre as nombre_ciudad')
                ->whereIn('s.id_sucursal', $sucursalesIds)
                ->get()
                ->keyBy('id_sucursal');

            $infoRetiro  = $nombresSucursales->get($reservacion->sucursal_retiro);
            $infoEntrega = $nombresSucursales->get($reservacion->sucursal_entrega);

            $lugarRetiro  = $infoRetiro  ? "{$infoRetiro->nombre_ciudad} - {$infoRetiro->nombre_sucursal}"   : '-';
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

            Log::info('[INFO] Correo de confirmación enviado', [
                'id_reservacion' => $reservacionId,
                'email'          => $reservacion->email_cliente,
                'tipo'           => $tipoPlanCorreo,
            ]);

            return true;
        } catch (\Throwable $e) {
            // No relanzar: la reserva ya está guardada, un fallo de correo no rompe el flujo
            Log::error('[ERROR] Falla enviando correo de confirmación (la reserva SÍ se guardó)', [
                'id_reservacion' => $reservacionId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Consulta a la API de PayPal para verificar que la orden existe,
     * está pagada, y los montos cuadran.
     *
     * FILOSOFÍA:
     * - PayPal NO cobró           → ['ok'=>false, 'cobro_realizado'=>false] → rechazar
     * - PayPal cobró, montos ok   → ['ok'=>true,  'cobro_realizado'=>true]  → guardar
     * - PayPal cobró, desajuste ≤ tolerancia → ['ok'=>true, ...] → guardar
     * - PayPal cobró, desajuste mayor        → ['ok'=>false, 'cobro_realizado'=>true] → guardar con marca de revisión
     */
    private function validarPagoPayPal(string $paypalOrderId, float $totalEsperado, ?float $totalLocal = null): array
    {
        $mode     = env('PAYPAL_MODE', 'live');
        $clientId = $mode === 'live' ? env('PAYPAL_CLIENT_ID_LIVE') : env('PAYPAL_CLIENT_ID_SANDBOX', env('PAYPAL_CLIENT_ID_LIVE'));
        $secret   = $mode === 'live' ? env('PAYPAL_SECRET_LIVE')    : env('PAYPAL_SECRET_SANDBOX',    env('PAYPAL_SECRET_LIVE'));
        $baseUrl  = $mode === 'live' ? 'https://api-m.paypal.com'   : 'https://api-m.sandbox.paypal.com';

        if (!$clientId || !$secret) {
            Log::error('[ERROR] Credenciales de PayPal incompletas en .env');
            return [
                'ok'              => false,
                'cobro_realizado' => false,
                'message'         => 'Configuración de PayPal incompleta. Intenta más tarde.',
            ];
        }

        try {
            $tokenResponse = Http::withBasicAuth($clientId, $secret)
                ->asForm()
                ->timeout(15)
                ->post("$baseUrl/v1/oauth2/token", ['grant_type' => 'client_credentials']);

            $accessToken = $tokenResponse['access_token'] ?? null;

            if (!$accessToken) {
                Log::error('[ERROR] PayPal sin access_token en respuesta OAuth', [
                    'json' => $tokenResponse->json(),
                ]);
                return [
                    'ok'              => false,
                    'cobro_realizado' => false,
                    'message'         => 'No se pudo obtener autorización de PayPal.',
                ];
            }

            $orderResponse = Http::withToken($accessToken)
                ->timeout(15)
                ->get("$baseUrl/v2/checkout/orders/$paypalOrderId");

            if (!$orderResponse->ok()) {
                return [
                    'ok'              => false,
                    'cobro_realizado' => false,
                    'message'         => 'No se pudo validar la orden de pago con PayPal.',
                ];
            }

            $orderData = $orderResponse->json();
            $status    = $orderData['status'] ?? '';

            if ($status !== 'COMPLETED') {
                return [
                    'ok'              => false,
                    'cobro_realizado' => false,
                    'message'         => 'El pago aún no está completado en PayPal.',
                    'status_paypal'   => $status,
                ];
            }

            // A partir de aquí, PayPal SÍ cobró
            $amountValue  = $orderData['purchase_units'][0]['amount']['value']         ?? null;
            $currencyCode = $orderData['purchase_units'][0]['amount']['currency_code'] ?? null;
            $montoPaypal  = (float) $amountValue;

            // Datos de la captura (para la tabla pagos)
            $captura    = $orderData['purchase_units'][0]['payments']['captures'][0] ?? [];
            $captureId  = $captura['id']          ?? null;
            $capturedAt = $captura['create_time'] ?? null;

            // Validación estricta de moneda
            if ($currencyCode !== 'MXN') {
                Log::warning('[WARN] Moneda incorrecta de PayPal', [
                    'currency' => $currencyCode,
                    'order_id' => $paypalOrderId,
                ]);
                return [
                    'ok'              => false,
                    'cobro_realizado' => true,
                    'monto_paypal'    => $montoPaypal,
                    'moneda_paypal'   => $currencyCode,
                    'capture_id'      => $captureId,
                    'captured_at'     => $capturedAt,
                    'order_data'      => $orderData,
                    'message'         => 'El pago se procesó en una moneda incorrecta.',
                ];
            }

            $diferencia = abs($montoPaypal - $totalEsperado);

            if ($diferencia <= self::TOLERANCIA_PAYPAL_MXN) {
                return [
                    'ok'              => true,
                    'cobro_realizado' => true,
                    'monto_paypal'    => $montoPaypal,
                    'monto_esperado'  => $totalEsperado,
                    'diferencia'      => $diferencia,
                    'moneda_paypal'   => $currencyCode,
                    'capture_id'      => $captureId,
                    'captured_at'     => $capturedAt,
                    'order_data'      => $orderData,
                ];
            }

            // Desajuste mayor a la tolerancia: cobró pero no cuadran
            Log::warning('[WARN] Desajuste de monto PayPal vs Backend', [
                'paypal'     => $montoPaypal,
                'backend'    => $totalEsperado,
                'frontend'   => $totalLocal,
                'diferencia' => $diferencia,
                'order_id'   => $paypalOrderId,
            ]);

            return [
                'ok'              => false,
                'cobro_realizado' => true,
                'monto_paypal'    => $montoPaypal,
                'monto_esperado'  => $totalEsperado,
                'monto_frontend'  => $totalLocal,
                'diferencia'      => $diferencia,
                'moneda_paypal'   => $currencyCode,
                'capture_id'      => $captureId,
                'captured_at'     => $capturedAt,
                'order_data'      => $orderData,
                'message'         => 'El monto del pago tiene un desajuste con la reservación. Se registró para revisión manual.',
            ];
        } catch (\Throwable $e) {
            Log::error('[ERROR] Excepción al validar PayPal', [
                'error'    => $e->getMessage(),
                'order_id' => $paypalOrderId,
            ]);
            return [
                'ok'              => false,
                'cobro_realizado' => false,
                'message'         => 'Error de conexión con PayPal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Genera un folio único MX-X000X0
     * (6.7M combinaciones; con maxIntentos=20 evita colisiones reales)
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
