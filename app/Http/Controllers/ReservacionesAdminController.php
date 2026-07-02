<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservacionAdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservacionAdminMail;

class ReservacionesAdminController extends Controller
{
    /**
     * 🧭 Vista principal de Reservaciones del administrador.
     */
    public function index(Request $request)
    {
        Log::channel('reservaciones')->withContext([
            'request_id' => (string) Str::uuid(),
            'asesor_id'  => session('id_usuario'),
            'ip'         => $request->ip(),
            'action'     => 'admin.ver_index_reservas',
        ]);

        try {
            // categoria
            $categorias = DB::table('categorias_carros as c')
                ->join('categoria_costo_km as ck', 'c.id_categoria', '=', 'ck.id_categoria')
                ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
                ->where('ck.activo', 1)
                ->select(
                    'c.id_categoria',
                    'c.codigo',
                    'c.nombre',
                    'c.descripcion',
                    'c.precio_dia',
                    'c.activo',
                    'ck.costo_km',
                    DB::raw('MAX(v.capacidad_tanque) as litros_maximos')
                )
                ->groupBy('c.id_categoria', 'c.codigo', 'c.nombre', 'c.descripcion', 'c.precio_dia', 'c.activo', 'ck.costo_km')
                ->orderBy('c.nombre')
                ->get();

            // Sucursales — DROPOFF (entrega): todas las activas, sin filtrar por ver_admin
            $sucursales = DB::table('sucursales as s')
                ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
                ->where('s.activo', 1)
                ->select(
                    's.id_sucursal',
                    's.nombre as sucursal',
                    'c.nombre as ciudad',
                    'c.id_ciudad'
                )
                ->orderByRaw("CASE WHEN c.nombre = 'Querétaro' THEN 0 ELSE 1 END")
                ->orderBy('c.nombre')
                ->orderBy('s.nombre')
                ->get()
                ->groupBy('ciudad');

            // Sucursales — PICKUP (retiro): solo las habilitadas para panel (ver_admin = 1)
            $sucursalesPickup = DB::table('sucursales as s')
                ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
                ->where('s.activo', 1)
                ->where('s.ver_admin', 1)
                ->select(
                    's.id_sucursal',
                    's.nombre as sucursal',
                    'c.nombre as ciudad',
                    'c.id_ciudad'
                )
                ->orderByRaw("CASE WHEN c.nombre = 'Querétaro' THEN 0 ELSE 1 END")
                ->orderBy('c.nombre')
                ->orderBy('s.nombre')
                ->get()
                ->groupBy('ciudad');

            // Ubicaciones
            $ubicaciones = DB::table('ubicaciones_servicio')
                ->where('activo', 1)
                ->orderBy('estado')
                ->orderBy('destino')
                ->get();

            // ===============================================================
            // 🧩 Servicios adicionales (cards dinámicas del carrusel)
            // Solo los habilitados para panel (administrador = 1).
            // Se EXCLUYE la fila 11 (Drop Off), porque Drop Off y Delivery
            // se manejan aparte como cards de ubicación (no salen de aquí).
            // Se calcula el ícono por nombre para no hardcodearlo en el Blade.
            // ===============================================================
            $serviciosAdicionales = DB::table('servicios')
                ->where('activo', 1)
                ->where('administrador', 1)
                ->where('id_servicio', '!=', 11)
                ->orderBy('id_servicio')
                ->get()
                ->map(function ($srv) {
                    $n = mb_strtolower($srv->nombre);

                    $srv->icon = match (true) {
                        str_contains($n, 'silla')     || str_contains($n, 'baby')   => 'fas fa-baby-carriage',
                        str_contains($n, 'conductor') || str_contains($n, 'driver') => 'fas fa-user-plus',
                        str_contains($n, 'gasolina')  || str_contains($n, 'fuel')   => 'fas fa-gas-pump',
                        str_contains($n, 'gps')                                     => 'fas fa-location-arrow',
                        str_contains($n, 'licencia')                                => 'fas fa-id-card',
                        str_contains($n, 'upgrade')   || str_contains($n, 'categor')=> 'fas fa-arrow-up',
                        str_contains($n, 'celular')   || str_contains($n, 'accesor')=> 'fas fa-mobile-screen',
                        str_contains($n, 'litro')                                   => 'fas fa-oil-can',
                        default => 'fas fa-circle-plus',
                    };

                    // Bandera para que el Blade sepa si esta card es de tipo tanque
                    // (Gasolina): switch + total, SIN control de cantidad.
                    $srv->es_tanque = ($srv->tipo_cobro === 'por_tanque');

                    return $srv;
                });

            $delivery = (object)['activo' => 0, 'total' => 0, 'kms' => 0, 'direccion' => '', 'id_ubicacion' => null];
            $costoKmCategoria = 0;
            $reservacion = (object)['id_reservacion' => null];

            // Seguros individuales
            $individuales = DB::table('seguro_individuales')
                ->select('id_individual', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
                ->where('activo', 1)
                ->orderBy('precio_por_dia')
                ->get();

            // 🔧 Normalizador de texto
            $norm = function ($s) {
                $s = mb_strtolower(trim((string)$s));
                $s = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
                    ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
                    $s
                );
                return $s;
            };

            // 🔎 Match por palabras clave (nombre + descripción)
            $match = function ($row, array $keys) use ($norm) {
                $text = $norm(($row->nombre ?? '') . ' ' . ($row->descripcion ?? ''));
                foreach ($keys as $k) {
                    if (str_contains($text, $norm($k))) {
                        return true;
                    }
                }
                return false;
            };

            // =====================================================
            // AGRUPACIÓN DE PROTECCIONES INDIVIDUALES
            // =====================================================

            // 1. COLISIÓN Y ROBO - LDW, PDW, CDW, DECLINE CDW
            $grupo_colision = $individuales->filter(function ($item) {
                $nombre = strtoupper($item->nombre);
                return str_contains($nombre, 'LDW') ||
                    str_contains($nombre, 'PDW') ||
                    str_contains($nombre, 'CDW') ||
                    str_contains($nombre, 'DECLINE CDW');
            })->values();

            // 2. GASTOS MÉDICOS - PAI
            $grupo_medicos = $individuales->filter(function ($item) {
                $nombre = strtoupper($item->nombre);
                return str_contains($nombre, 'PAI');
            })->values();

            // 3. ASISTENCIA PARA EL CAMINO - PRA
            $grupo_asistencia = $individuales->filter(function ($item) {
                $nombre = strtoupper($item->nombre);
                return str_contains($nombre, 'PRA');
            })->values();

            // 4. DAÑOS A TERCEROS - SOLO LI, ALI, EXT. LI
            $grupo_terceros = $individuales->filter(function ($item) {
                $nombre = strtoupper($item->nombre);
                return $nombre === 'LI' ||
                    $nombre === 'ALI' ||
                    $nombre === 'EXT. LI' ||
                    str_contains($nombre, 'LIABILITY') ||
                    str_contains($nombre, 'LI (') ||
                    str_contains($nombre, 'ALI (') ||
                    str_contains($nombre, 'EXT. LI');
            })->values();

            // 5. PROTECCIONES AUTOMÁTICAS - LOU, LA (el resto)
            $idsUsados = collect()
                ->merge($grupo_colision->pluck('id_individual'))
                ->merge($grupo_medicos->pluck('id_individual'))
                ->merge($grupo_asistencia->pluck('id_individual'))
                ->merge($grupo_terceros->pluck('id_individual'))
                ->unique();

            $grupo_protecciones = $individuales
                ->filter(fn($r) => !$idsUsados->contains($r->id_individual))
                ->values();

            return view('Admin.reservaciones', compact(
                'categorias',
                'sucursales',
                'sucursalesPickup',
                'grupo_colision',
                'grupo_medicos',
                'grupo_asistencia',
                'grupo_terceros',
                'grupo_protecciones',
                'ubicaciones',
                'serviciosAdicionales',
                'delivery',
                'costoKmCategoria',
                'reservacion'
            ));
        } catch (\Throwable $e) {
            Log::channel('reservaciones')->error('Error critico al cargar vista reservaciones', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ]);

            abort(500);
        }
    }

    /**
     * 🚗 Obtener información de una categoría
     */
    public function obtenerCategoriaPorId(Request $request, $idCategoria)
    {
        Log::channel('reservaciones')->withContext([
            'request_id' => (string) Str::uuid(),
            'asesor_id'  => session('id_usuario'),
            'target_id'  => $idCategoria
        ]);

        try {
            $categoria = DB::table('categorias_carros as c')
                ->leftJoin('categoria_costo_km as ck', 'c.id_categoria', '=', 'ck.id_categoria')
                ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
                ->leftJoin('vehiculo_imagenes as img', 'v.id_vehiculo', '=', 'img.id_vehiculo')
                ->where('c.id_categoria', $idCategoria)
                ->select(
                    'c.id_categoria',
                    'c.codigo',
                    'c.nombre',
                    'c.descripcion',
                    'c.precio_dia as tarifa_base',
                    'ck.costo_km',
                    DB::raw('COALESCE(img.url, "/assets/Logotipo.png") as imagen')
                )
                ->first();

            if (!$categoria) {
                return response()->json(['error' => true, 'message' => 'Categoría no encontrada.'], 404);
            }

            $maxTanque = DB::table('vehiculos')
                ->where('id_categoria', $idCategoria)
                ->max('capacidad_tanque') ?? 0;

            $categoria->capacidad_maxima = (float)$maxTanque;

            return response()->json($categoria);
        } catch (\Throwable $e) {
            Log::channel('reservaciones')->error('Error SQL al buscar categoria', [
                'message' => $e->getMessage()
            ]);

            return response()->json(['error' => true, 'message' => 'Error interno'], 500);
        }
    }

    /**
     * 🛡️ Paquetes de seguros
     */
    public function getSeguros()
    {
        return response()->json(
            DB::table('seguro_paquete')
                ->where('activo', 1)
                ->orderBy('precio_por_dia')
                ->get()
        );
    }

    /**
     * 🧩 Servicios adicionales
     */
    public function getServicios()
    {
        return response()->json(
            DB::table('servicios')
                ->where('activo', 1)
                ->orderBy('precio')
                ->get()
        );
    }

    /**
     * 💾 Guardar reservación
     */
    public function guardarReservacion(StoreReservacionAdminRequest $request)
    {
        $requestId = (string) Str::uuid();

        $log = Log::channel('reservaciones')->withContext([
            'request_id' => $requestId,
            'asesor_id'  => session('id_usuario'),
            'ip'         => $request->ip(),
            'action'     => 'admin.crear_reserva_guardar',
            'email_cliente' => $request->input('email_cliente')
        ]);

        $log->info('Iniciando transaccion de guardado', [
            'inputs' => $request->except(['_token'])
        ]);

        // 🐞 DEBUG temporal: confirma si llegan las individuales
        $log->info('DEBUG individuales', [
            'recibido' => $request->input('individualesSeleccionados'),
        ]);

        try {

            // 👤 Asesor logueado (usuario admin del sistema)
            $idAsesor = session('id_usuario');

            if (!$idAsesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado'
                ], 401);
            }

            // ✅ Validación
            $validated = $request->validated();

            // 🔎 Sucursales → ciudades
            $sucursalRetiro = DB::table('sucursales')
                ->where('id_sucursal', $validated['sucursal_retiro'])
                ->first();

            $sucursalEntrega = DB::table('sucursales')
                ->where('id_sucursal', $validated['sucursal_entrega'])
                ->first();

            if (!$sucursalRetiro || !$sucursalEntrega) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sucursal de retiro o entrega inválida',
                ], 422);
            }

            $ciudadRetiroId  = $sucursalRetiro->id_ciudad;
            $ciudadEntregaId = $sucursalEntrega->id_ciudad;

            // 💰 Categoría
            $categoria = DB::table('categorias_carros')
                ->where('id_categoria', $validated['id_categoria'])
                ->first();

            // ✅ Ficha "Tu Auto"
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
            $tuAuto = $cap;

            // 👉 Tarifa base
            $precioOriginal = (float) $categoria->precio_dia;
            $precioParaCobrar = $precioOriginal;

            $colTarifaModificada = null;
            $colTarifaAjustada   = 0;

            if ($request->filled('tarifa_base')) {
                $precioEnviado = (float) $request->input('tarifa_base');

                if (abs($precioEnviado - $precioOriginal) > 0.01) {
                    $precioParaCobrar    = $precioEnviado;
                    $colTarifaModificada = $precioEnviado;
                    $colTarifaAjustada   = 1;
                }
            }

            // ===============================
            // ✅ Días base por diferencia de fechas
            // ===============================
            $diasBase = Carbon::parse($validated['fecha_inicio'])
                ->diffInDays(Carbon::parse($validated['fecha_fin']));

            // Cortesía de 1 hora: si la hora de devolución pasa de
            // la hora de pick-up + 1h, se cobra un día extra.
            $horaRetiroNum  = (int) explode(':', (string) $request->input('hora_retiro'))[0];
            $horaEntregaNum = (int) explode(':', (string) $request->input('hora_entrega'))[0];

            if ($horaEntregaNum > $horaRetiroNum + 1) {
                $diasBase += 1;
            }

            $dias = max(1, $diasBase);

            // ✅ Adicionales (total)
            $extrasServiciosTotal = 0.0;

            if ($request->filled('adicionalesSeleccionados')) {
                $extras = $request->input('adicionalesSeleccionados');

                if (is_array($extras)) {
                    foreach ($extras as $extra) {
                        // 🔧 NOMBRE CORREGIDO: el JS manda 'precio_unitario', no 'precio'
                        if (!is_array($extra) || !isset($extra['precio_unitario'])) {
                            continue;
                        }

                        $precio   = (float) ($extra['precio_unitario'] ?? 0); // precio por día
                        $cantidad = (int)   ($extra['cantidad'] ?? 1);        // cantidad seleccionada

                        // opciones por día
                        $extrasServiciosTotal += $precio * $cantidad * $dias;
                    }
                }
            }

            // ✅ Paquete de seguro (total)
            $seguroTotal = 0.0;
            if ($request->filled('seguroSeleccionado.id')) {
                $seguro = $request->input('seguroSeleccionado');
                if (is_array($seguro) && isset($seguro['precio'])) {
                    $seguroTotal = (float) $seguro['precio'] * $dias;
                }
            }

            // ===============================
            // ✅ Seguros individuales (por día) desde el request
            //    El JS manda 'id' y 'precio' en individualesSeleccionados
            // ===============================
            $individualesTotal = 0.0;

            if ($request->filled('individualesSeleccionados')) {
                $individuales = $request->input('individualesSeleccionados');

                if (is_array($individuales)) {
                    foreach ($individuales as $ind) {
                        if (!is_array($ind) || !isset($ind['precio'])) {
                            continue;
                        }

                        $precioInd = (float) ($ind['precio'] ?? 0); // precio por día
                        $individualesTotal += $precioInd * $dias;
                    }
                }
            }

            // ✅ Total de OPCIONES por toda la renta
            $opcionesRentaTotal = round($seguroTotal + $extrasServiciosTotal + $individualesTotal, 2);

            // Delivery
            $deliveryActivo = $request->input('delivery_activo', 0) == 1 ? 1 : 0;
            $deliveryTotal  = $deliveryActivo ? (float) $request->input('delivery_total', 0) : 0;

            // Dropoff
            $dropoffActivo = $request->input('dropoff_activo', 0) == 1 ? 1 : 0;
            $dropoffTotal  = $dropoffActivo ? (float) $request->input('dropoff_total', 0) : 0;

            // Gasolina
            $gasolinaActiva = $request->input('svc_gasolina', 0) == 1;
            $gasolinaTotal  = 0.0;
            $capacidadMax   = 0;

            if ($gasolinaActiva) {
                $capacidadMax = DB::table('vehiculos')
                    ->where('id_categoria', $validated['id_categoria'])
                    ->max('capacidad_tanque') ?? 0;
                $gasolinaTotal = round($capacidadMax * 20.00, 2);
            }

            // ✅ Totales
            $tarifaBaseTotal = round($precioParaCobrar * $dias, 2);
            $subtotal = $tarifaBaseTotal + $opcionesRentaTotal + $deliveryTotal + $dropoffTotal + $gasolinaTotal;
            $iva      = round($subtotal * 0.16, 2);
            $total    = $subtotal + $iva;

            $codigo = $this->generarFolioReservacionUnico();

            // 💾 Insert principal
            $id = DB::table('reservaciones')->insertGetId([
                'id_usuario'        => null,
                'id_asesor'         => $idAsesor,
                'id_vehiculo'       => null,
                'id_categoria'      => $validated['id_categoria'],
                'nombre_cliente'    => $validated['nombre_cliente'],
                'email_cliente'     => $validated['email_cliente'],
                'telefono_cliente'  => $validated['telefono_cliente'],
                'comentarios'       => $request->input('comentarios'),
                'ciudad_retiro'     => $ciudadRetiroId,
                'ciudad_entrega'    => $ciudadEntregaId,
                'sucursal_retiro'   => $validated['sucursal_retiro'],
                'sucursal_entrega'  => $validated['sucursal_entrega'],
                'fecha_inicio'      => $validated['fecha_inicio'],
                'hora_retiro'       => $request->input('hora_retiro'),
                'fecha_fin'         => $validated['fecha_fin'],
                'hora_entrega'      => $request->input('hora_entrega'),
                'tarifa_base'       => $precioOriginal,
                'tarifa_modificada' => $colTarifaModificada,
                'tarifa_ajustada'   => $colTarifaAjustada,
                'subtotal'          => $subtotal,
                'impuestos'         => $iva,
                'total'             => $total,
                'codigo'            => $codigo,
                'estado'            => 'pendiente_pago',
                'delivery_activo'    => $deliveryActivo,
                'delivery_ubicacion' => $request->input('delivery_ubicacion'),
                'delivery_direccion' => $request->input('delivery_direccion'),
                'delivery_km'        => $request->input('delivery_km', 0),
                'delivery_precio_km' => $request->input('delivery_precio_km', 0),
                'delivery_total'     => $deliveryTotal,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Dropoff
            if ($request->input('dropoff_activo') == 1) {
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $id,
                    'id_servicio'     => 11,
                    'cantidad'        => 1,
                    'precio_unitario' => $request->input('dropoff_total'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // Gasolina
            if ($gasolinaActiva) {
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $id,
                    'id_servicio'     => 1,
                    'cantidad'        => $capacidadMax,
                    'precio_unitario' => 20.00,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            /* 4.1️⃣ Guardar paquete de seguro */
            if ($request->filled('seguroSeleccionado.id')) {
                $seguro = $request->input('seguroSeleccionado');
                if (is_array($seguro) && isset($seguro['id'])) {
                    DB::table('reservacion_paquete_seguro')->insert([
                        'id_reservacion' => $id,
                        'id_paquete'     => $seguro['id'],
                        'precio_por_dia' => $seguro['precio'] ?? 0,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }

            /* 4.2️⃣ Guardar servicios adicionales */
            if ($request->filled('adicionalesSeleccionados')) {
                $extras = $request->input('adicionalesSeleccionados');
                if (is_array($extras)) {
                    foreach ($extras as $extra) {
                        // 🔧 NOMBRE CORREGIDO: el JS manda 'id_servicio', no 'id'
                        if (!is_array($extra) || !isset($extra['id_servicio'])) {
                            continue;
                        }
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => $extra['id_servicio'],
                            'cantidad'        => $extra['cantidad'] ?? 1,
                            'precio_unitario' => $extra['precio_unitario'] ?? 0,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }

            /* ==========================================================
                4.3️⃣ Guardar seguros individuales (reservacion_seguro_individual)
                El JS manda 'id' y 'precio' en individualesSeleccionados
            ========================================================== */
            if ($request->filled('individualesSeleccionados')) {
                $individuales = $request->input('individualesSeleccionados');

                if (is_array($individuales)) {
                    foreach ($individuales as $ind) {
                        if (!is_array($ind) || !isset($ind['id'])) {
                            continue;
                        }

                        DB::table('reservacion_seguro_individual')->insert([
                            'id_reservacion' => $id,
                            'id_individual'  => $ind['id'],
                            'precio_por_dia' => $ind['precio'] ?? 0,
                            'cantidad'       => $ind['cantidad'] ?? 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            /* ==========================================================
                5️⃣ Enviar correo con Mailable (ReservacionAdminMail)
             ========================================================== */
            $correoCliente = $validated['email_cliente'] ?? null;
            $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');

            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            $seguroReserva = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as sp', 'sp.id_paquete', '=', 'rps.id_paquete')
                ->where('rps.id_reservacion', $id)
                ->select('sp.id_paquete', 'sp.nombre', 'sp.descripcion', 'rps.precio_por_dia')
                ->first();

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

            $catId = (int)($categoria->id_categoria ?? 0);
            $baseUrl = rtrim(config('app.url'), '/');
            $imgPath = $catImages[$catId] ?? 'img/categorias/placeholder.png';
            $imgCategoria = $baseUrl . '/' . ltrim($imgPath, '/');

            $extrasServiciosTotal = 0;
            if (!empty($extrasReserva)) {
                $extrasServiciosTotal = (float) $extrasReserva->sum('total');
            }

            $seguroTotal = 0;
            if (!empty($seguroReserva) && isset($seguroReserva->precio_por_dia)) {
                $seguroTotal = (float)$seguroReserva->precio_por_dia * (float)$dias;
            }

            $opcionesRentaTotal = round($seguroTotal + $extrasServiciosTotal + $deliveryTotal + $dropoffTotal, 2);

            try {
                if ($correoCliente) {
                    Mail::to($correoCliente)
                        ->cc($correoEmpresa)
                        ->send(new ReservacionAdminMail($reservacion, $categoria, $seguroReserva, $extrasReserva, $lugarRetiro, $lugarEntrega, $imgCategoria, $opcionesRentaTotal, $tuAuto));
                } else {
                    Mail::to($correoEmpresa)
                        ->send(new ReservacionAdminMail($reservacion, $categoria, $seguroReserva, $extrasReserva, $lugarRetiro, $lugarEntrega, $imgCategoria, $opcionesRentaTotal, $tuAuto));
                }
            } catch (\Throwable $e) {
                Log::error('❌ Error al enviar correo de reserva: ' . $e->getMessage());
            }

            // 6️⃣ Respuesta JSON
            return response()->json([
                'success'   => true,
                'message'   => 'Reservación creada correctamente y correo enviado.',
                'id'        => $id,
                'codigo'    => $codigo,
                'subtotal'  => $subtotal,
                'impuestos' => $iva,
                'total'     => $total,
                'estado'    => 'pendiente_pago',
            ]);
        } catch (\Throwable $e) {
            $log->error('Error critico al guardar reservacion', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la reservación.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================================
    // ✅ Generación de folio: MX- + L + NNN + L + N
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
     * Genera un folio único (reintenta para evitar colisiones).
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

        throw new \RuntimeException('No se pudo generar un folio único para la reservación.');
    }

    /**
     * ✏️ Editar desde activos
     */
    public function editar($id)
    {
        // 🔹 RESERVACIÓN
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!$reservacion) {
            abort(404, 'Reservación no encontrada');
        }

        // 🔵 SUCURSALES
        $sucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.activo', 1)
            ->select(
                's.id_sucursal',
                's.nombre as sucursal',
                'c.nombre as ciudad',
                'c.id_ciudad'
            )
            ->orderByRaw("CASE WHEN c.nombre = 'Querétaro' THEN 0 ELSE 1 END")
            ->orderBy('c.nombre')
            ->orderBy('s.nombre')
            ->get()
            ->groupBy('ciudad');

        // 🟣 CATEGORÍAS
        $categorias = DB::table('categorias_carros as c')
            ->join('categoria_costo_km as ck', 'c.id_categoria', '=', 'ck.id_categoria')
            ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
            ->where('ck.activo', 1)
            ->select(
                'c.id_categoria',
                'c.codigo',
                'c.nombre',
                'c.descripcion',
                'c.precio_dia',
                'c.activo',
                'ck.costo_km',
                DB::raw('MAX(v.capacidad_tanque) as litros_maximos')
            )
            ->groupBy(
                'c.id_categoria',
                'c.codigo',
                'c.nombre',
                'c.descripcion',
                'c.precio_dia',
                'c.activo',
                'ck.costo_km'
            )
            ->orderBy('c.nombre')
            ->get();

        // 🟡 UBICACIONES
        $ubicaciones = DB::table('ubicaciones_servicio')
            ->where('activo', 1)
            ->orderBy('estado')
            ->orderBy('destino')
            ->get();

        // 🔵 SERVICIOS
        $serviciosReserva = DB::table('reservacion_servicio as rs')
            ->join('servicios as s', 's.id_servicio', '=', 'rs.id_servicio')
            ->where('rs.id_reservacion', $id)
            ->select(
                's.id_servicio',
                's.nombre',
                'rs.cantidad',
                'rs.precio_unitario'
            )
            ->get();

        // 🟣 SEGURO (PAQUETE)
        $seguroReserva = DB::table('reservacion_paquete_seguro as rps')
            ->join('seguro_paquete as sp', 'sp.id_paquete', '=', 'rps.id_paquete')
            ->where('rps.id_reservacion', $id)
            ->select(
                'sp.id_paquete',
                'sp.nombre',
                'sp.descripcion',
                'rps.precio_por_dia'
            )
            ->first();

        // 🟢 PROTECCIONES INDIVIDUALES guardadas
        $individualesReserva = DB::table('reservacion_seguro_individual as rsi')
            ->join('seguro_individuales as si', 'si.id_individual', '=', 'rsi.id_individual')
            ->where('rsi.id_reservacion', $id)
            ->select(
                'si.id_individual',
                'si.nombre',
                'si.descripcion',
                'rsi.precio_por_dia'
            )
            ->get();

        // 🔴 DELIVERY
        $delivery = (object)[
            'activo' => $reservacion->delivery_activo ?? 0,
            'total' => $reservacion->delivery_total ?? 0,
            'kms' => $reservacion->delivery_km ?? 0,
            'direccion' => $reservacion->delivery_direccion ?? '',
            'id_ubicacion' => $reservacion->delivery_ubicacion ?? null,
        ];

        $costoKmCategoria = 0;

        return view('Admin.reservaciones', [
            'reservacion'         => $reservacion,
            'sucursales'          => $sucursales,
            'categorias'          => $categorias,
            'ubicaciones'         => $ubicaciones,
            'delivery'            => $delivery,
            'costoKmCategoria'    => $costoKmCategoria,
            'serviciosReserva'    => $serviciosReserva,
            'seguroReserva'       => $seguroReserva,
            'individualesReserva' => $individualesReserva,
        ]);
    }

    /**
     * 🔄 Actualizar reservación
     */
    public function update(Request $request, $id)
    {
        // 🔹 Reservación actual
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!$reservacion) {
            abort(404, 'Reservación no encontrada');
        }

        // 🔹 Sucursales → ciudades
        $sucursalRetiro = DB::table('sucursales')
            ->where('id_sucursal', $request->sucursal_retiro)
            ->first();

        $sucursalEntrega = DB::table('sucursales')
            ->where('id_sucursal', $request->sucursal_entrega)
            ->first();

        $ciudadRetiroId  = $sucursalRetiro->id_ciudad ?? null;
        $ciudadEntregaId = $sucursalEntrega->id_ciudad ?? null;

        // 🔹 Categoría
        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $request->id_categoria)
            ->first();

        // 🔹 Días base por diferencia de fechas
        $diasBase = \Carbon\Carbon::parse($request->fecha_inicio)
            ->diffInDays(\Carbon\Carbon::parse($request->fecha_fin));

        // Cortesía de 1 hora
        $horaRetiroNum  = (int) explode(':', (string) $request->hora_retiro)[0];
        $horaEntregaNum = (int) explode(':', (string) $request->hora_entrega)[0];

        if ($horaEntregaNum > $horaRetiroNum + 1) {
            $diasBase += 1;
        }

        $dias = max(1, $diasBase);

        // ===============================
        // 🔥 BASE
        // ===============================
        $precioDia = (float) $categoria->precio_dia;
        $tarifaBaseTotal = $precioDia * $dias;

        // ===============================
        // 🔥 EXTRAS (ADICIONALES)
        // ===============================
        $extrasServiciosTotal = 0;

        if ($request->filled('adicionalesSeleccionados')) {
            foreach ($request->input('adicionalesSeleccionados') as $extra) {
                // 🔧 NOMBRE CORREGIDO: el JS manda 'precio_unitario', no 'precio'
                if (!is_array($extra) || !isset($extra['precio_unitario'])) {
                    continue;
                }

                $precio   = (float) $extra['precio_unitario'];
                $cantidad = (int) ($extra['cantidad'] ?? 1);

                $extrasServiciosTotal += $precio * $cantidad * $dias;
            }
        }

        // ===============================
        // 🔥 SEGURO (PAQUETE)
        // ===============================
        $seguroTotal = 0;
        if ($request->filled('seguroSeleccionado.precio')) {
            $seguroTotal = (float)$request->input('seguroSeleccionado.precio') * $dias;
        }

        // ===============================
        // 🔥 SEGUROS INDIVIDUALES
        //    El JS manda 'id' y 'precio' en individualesSeleccionados
        // ===============================
        $individualesTotal = 0;
        if ($request->filled('individualesSeleccionados')) {
            foreach ((array) $request->input('individualesSeleccionados') as $ind) {
                if (is_array($ind) && isset($ind['precio'])) {
                    $individualesTotal += (float) $ind['precio'] * $dias;
                }
            }
        }

        // ===============================
        // 🔥 DELIVERY
        // ===============================
        $deliveryTotal = $request->delivery_activo == 1
            ? (float)$request->delivery_total
            : 0;

        // ===============================
        // 🔥 DROPOFF
        // ===============================
        $dropoffTotal = $request->dropoff_activo == 1
            ? (float)$request->dropoff_total
            : 0;

        // ===============================
        // 🔥 GASOLINA
        // ===============================
        $gasolinaTotal = 0;
        if ($request->svc_gasolina == 1) {
            $litros = DB::table('vehiculos')
                ->where('id_categoria', $request->id_categoria)
                ->max('capacidad_tanque') ?? 0;
            $gasolinaTotal = $litros * 20;
        }

        // ===============================
        // 🔥 OPCIONES DE RENTA
        // ===============================
        $opcionesRentaTotal = $seguroTotal + $extrasServiciosTotal + $individualesTotal;

        // ===============================
        // 🔥 TOTAL FINAL
        // ===============================
        $subtotal = $tarifaBaseTotal
            + $opcionesRentaTotal
            + $deliveryTotal
            + $dropoffTotal
            + $gasolinaTotal;

        $iva = round($subtotal * 0.16, 2);
        $total = $subtotal + $iva;

        $tarifaBaseGuardar = (float) $categoria->precio_dia;

        // =========================
        // 🔥 UPDATE PRINCIPAL
        // =========================
        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'id_categoria'      => $request->id_categoria,
                'ciudad_retiro'     => $ciudadRetiroId,
                'ciudad_entrega'    => $ciudadEntregaId,
                'sucursal_retiro'   => $request->sucursal_retiro,
                'sucursal_entrega'  => $request->sucursal_entrega,
                'nombre_cliente'    => $request->nombre_cliente,
                'email_cliente'     => $request->email_cliente,
                'telefono_cliente'  => $request->telefono_cliente,
                'comentarios'       => $request->input('comentarios'),
                'fecha_inicio'      => $request->fecha_inicio,
                'hora_retiro'       => $request->hora_retiro,
                'fecha_fin'         => $request->fecha_fin,
                'hora_entrega'      => $request->hora_entrega,
                'tarifa_base'       => $tarifaBaseGuardar,
                'delivery_activo'   => $request->delivery_activo,
                'delivery_total'    => $deliveryTotal,
                'delivery_km'       => $request->delivery_km,
                'delivery_direccion' => $request->delivery_direccion,
                'delivery_ubicacion' => $request->delivery_ubicacion,
                'subtotal'          => $subtotal,
                'impuestos'         => $iva,
                'total'             => $total,
                'updated_at'        => now(),
            ]);

        // =========================
        // 🔥 SERVICIOS (RESET)
        // =========================
        DB::table('reservacion_servicio')
            ->where('id_reservacion', $id)
            ->delete();

        // 🚩 DROPOFF
        if ($request->dropoff_activo == 1) {
            DB::table('reservacion_servicio')->insert([
                'id_reservacion'  => $id,
                'id_servicio'     => 11,
                'cantidad'        => 1,
                'precio_unitario' => $request->dropoff_total,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ⛽ GASOLINA
        if ($request->svc_gasolina == 1) {
            DB::table('reservacion_servicio')->insert([
                'id_reservacion'  => $id,
                'id_servicio'     => 1,
                'cantidad'        => 1,
                'precio_unitario' => 20,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ➕ ADICIONALES
        if ($request->filled('adicionalesSeleccionados')) {
            foreach ($request->input('adicionalesSeleccionados') as $extra) {
                // 🔧 NOMBRE CORREGIDO: el JS manda 'id_servicio' y 'precio_unitario'
                if (!is_array($extra) || !isset($extra['id_servicio'])) {
                    continue;
                }
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $id,
                    'id_servicio'     => $extra['id_servicio'],
                    'cantidad'        => $extra['cantidad'] ?? 1,
                    'precio_unitario' => $extra['precio_unitario'] ?? 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // =========================
        // 🔥 SEGURO (RESET)
        // =========================
        DB::table('reservacion_paquete_seguro')
            ->where('id_reservacion', $id)
            ->delete();

        if ($request->filled('seguroSeleccionado.id')) {
            DB::table('reservacion_paquete_seguro')->insert([
                'id_reservacion' => $id,
                'id_paquete'     => $request->input('seguroSeleccionado.id'),
                'precio_por_dia' => $request->input('seguroSeleccionado.precio'),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // =========================
        // 🔥 SEGUROS INDIVIDUALES (RESET)
        //    El JS manda 'id' y 'precio' en individualesSeleccionados
        // =========================
        DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $id)
            ->delete();

        if ($request->filled('individualesSeleccionados')) {
            foreach ($request->input('individualesSeleccionados') as $ind) {
                if (!is_array($ind) || !isset($ind['id'])) {
                    continue;
                }
                DB::table('reservacion_seguro_individual')->insert([
                    'id_reservacion' => $id,
                    'id_individual'  => $ind['id'],
                    'precio_por_dia' => $ind['precio'] ?? 0,
                    'cantidad'       => $ind['cantidad'] ?? 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        return redirect()->route('rutaReservacionesActivas')
            ->with('success', 'Reservación actualizada correctamente');
    }
}
