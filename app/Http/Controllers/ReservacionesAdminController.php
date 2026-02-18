<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        // ===============================
        // CATEGORÍAS
        // ===============================
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia', 'activo')
            ->orderBy('nombre')
            ->get();
        // ===============================
        // SUCURSALES
        // ===============================
       $sucursales = DB::table('sucursales as s')
           ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
           ->where('s.activo', 1)
           ->select(
        's.id_sucursal',
        's.nombre as nombre',
        'c.nombre as ciudad',
        'c.id_ciudad',
        DB::raw("CASE
            WHEN LOWER(s.nombre) LIKE '%aeropuerto%'
            THEN 1 ELSE 0
        END as es_aeropuerto")
    )


           ->orderByRaw("CASE WHEN c.nombre = 'QUERETARO' THEN 0 ELSE 1 END")
           ->orderBy('c.nombre')
           ->orderBy('s.nombre')
           ->get();



        // =====================================================
        // ✅ SEGUROS INDIVIDUALES (TU TABLA REAL)
        // =====================================================
        $individuales = DB::table('seguro_individuales')
            ->select('id_individual', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
            ->where('activo', 1)
            ->orderBy('precio_por_dia')
            ->get();

        // 🔧 Normalizador de texto
        $norm = function ($s) {
            $s = mb_strtolower(trim((string)$s));
            $s = str_replace(
                ['á','é','í','ó','ú','ü','ñ'],
                ['a','e','i','o','u','u','n'],
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
        // AGRUPACIÓN REAL SEGÚN TU DATA
        // =====================================================
        $grupo_colision = $individuales->filter(fn($r) => $match($r, [
            'LDW', 'PDW', 'CDW', 'collision', 'damage waiver',
            'loss damage', 'robo', 'theft', 'decline cdw'
        ]))->values();

        $grupo_medicos = $individuales->filter(fn($r) => $match($r, [
            'PAI', 'personal accident', 'gastos medicos',
            'medico', 'medical'
        ]))->values();

        $grupo_asistencia = $individuales->filter(fn($r) => $match($r, [
            'PRA', 'road assistance', 'asistencia',
            'carretera', 'camino'
        ]))->values();

        $grupo_terceros = $individuales->filter(fn($r) => $match($r, [
            'LI', 'liability', 'responsabilidad civil',
            'terceros'
        ]))->values();

        // Todo lo demás va como automáticas
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
            'grupo_colision',
            'grupo_medicos',
            'grupo_asistencia',
            'grupo_terceros',
            'grupo_protecciones'
        ));
    }

    /**
     * 🚗 Obtener información de una categoría
     */
    public function obtenerCategoriaPorId($idCategoria)
    {
        try {
            $categoria = DB::table('categorias_carros as c')
                ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
                ->leftJoin('vehiculo_imagenes as img', 'v.id_vehiculo', '=', 'img.id_vehiculo')
                ->where('c.id_categoria', $idCategoria)
                ->select(
                    'c.id_categoria',
                    'c.codigo',
                    'c.nombre',
                    'c.descripcion',
                    'c.precio_dia as tarifa_base',
                    DB::raw('COALESCE(img.url, "/assets/Logotipo.png") as imagen')
                )
                ->first();

            if (!$categoria) {
                return response()->json(['error' => true, 'message' => 'Categoría no encontrada.'], 404);
            }

            return response()->json($categoria);
        } catch (\Throwable $e) {
            Log::error('❌ Error al obtener categoría: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Error interno.'], 500);
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
    public function guardarReservacion(Request $request)
{
    try {
        // 👤 Asesor logueado (usuario admin del sistema)
        $idAsesor = session('id_usuario');

        if (!$idAsesor) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        // ✅ Validación: categoría, fechas, sucursales y datos del cliente
        $validated = $request->validate([
            'id_categoria'      => 'required|exists:categorias_carros,id_categoria',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'required|date|after_or_equal:fecha_inicio',

            'sucursal_retiro'   => 'required|integer|exists:sucursales,id_sucursal',
            'sucursal_entrega'  => 'required|integer|exists:sucursales,id_sucursal',

            'nombre_cliente'    => 'required|string|max:150',
            'apellidos_cliente' => 'required|string|max:150',
            'email_cliente'     => 'required|email|max:150',
            'telefono_cliente'  => 'required|string|max:30',
            'telefono_lada'     => 'nullable|string|max:10', // solo se valida, no se guarda si no existe la columna
        ]);

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

        // 💰 Cálculo de totales (con tarifa base)
        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $validated['id_categoria'])
            ->first();
            // ===============================
// ✅ Ficha "Tu Auto" (como Catálogo)
// ===============================
$predeterminados = [
    'C'  => ['pax'=>5,  'small'=>2, 'big'=>1],
    'D'  => ['pax'=>5,  'small'=>2, 'big'=>1],
    'E'  => ['pax'=>5,  'small'=>2, 'big'=>2],
    'F'  => ['pax'=>5,  'small'=>2, 'big'=>2],
    'IC' => ['pax'=>5,  'small'=>2, 'big'=>2],
    'I'  => ['pax'=>5,  'small'=>3, 'big'=>2],
    'IB' => ['pax'=>7,  'small'=>3, 'big'=>2],
    'M'  => ['pax'=>7,  'small'=>4, 'big'=>2],
    'L'  => ['pax'=>13, 'small'=>4, 'big'=>3],
    'H'  => ['pax'=>5,  'small'=>3, 'big'=>2],
    'HI' => ['pax'=>5,  'small'=>3, 'big'=>2],
];

$codigoCat = strtoupper(trim((string)($categoria->codigo ?? '')));
$cap = $predeterminados[$codigoCat] ?? ['pax'=>5,'small'=>2,'big'=>1];

// Nombre en singular para subtítulo (Grandes -> GRANDE, Medianos -> MEDIANO, etc.)
$nombreCat = trim((string)($categoria->nombre ?? ''));
$singular = $nombreCat;
if (mb_substr($singular, -1) === 's') {
    $singular = mb_substr($singular, 0, mb_strlen($singular)-1);
}
$singular = mb_strtoupper($singular);

// El título grande debe ser la descripción (ej: "Volkswagen Jetta o similar")
$tituloAuto = trim((string)($categoria->descripcion ?? 'Auto o similar'));

// Subtítulo tipo: "GRANDE | CATEGORÍA E"
$subtituloAuto = $singular . " | CATEGORÍA " . ($codigoCat ?: '-');

$tuAuto = [
    'titulo'     => $tituloAuto,
    'subtitulo'  => $subtituloAuto,
    'pax'        => (int)$cap['pax'],
    'small'      => (int)$cap['small'],
    'big'        => (int)$cap['big'],
    'transmision'=> 'Transmisión manual o automática',
    'tech'       => 'Apple CarPlay | Android Auto',
    'incluye'    => 'KM ilimitados | Reelevo de Responsabilidad (LI)',
];


        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada',
            ], 404);
        }

        // 👉 Tarifa base que viene de categorias_carros
$tarifaBase = (float) $categoria->precio_dia;

$dias = max(
    1,
    Carbon::parse($validated['fecha_inicio'])
        ->diffInDays(Carbon::parse($validated['fecha_fin']))
);

// ===============================
// ✅ Calcular total de OPCIONES desde el request
//    (seguro + servicios), ambos por día
// ===============================
$extrasServiciosTotal = 0.0;

if ($request->filled('adicionalesSeleccionados')) {
    $extras = $request->input('adicionalesSeleccionados');

    if (is_array($extras)) {
        foreach ($extras as $extra) {
            if (!is_array($extra) || !isset($extra['precio'])) {
                continue;
            }

            $precio   = (float) ($extra['precio'] ?? 0);   // precio por día
            $cantidad = (int)   ($extra['cantidad'] ?? 1); // cantidad seleccionada

            // opciones por día
            $extrasServiciosTotal += $precio * $cantidad * $dias;
        }
    }
}

$seguroTotal = 0.0;
if ($request->filled('seguroSeleccionado.id')) {
    $seguro = $request->input('seguroSeleccionado');

    if (is_array($seguro) && isset($seguro['precio'])) {
        // precio del paquete por día
        $seguroTotal = (float) $seguro['precio'] * $dias;
    }
}

// ✅ Total de OPCIONES por toda la renta
$opcionesRentaTotal = round($seguroTotal + $extrasServiciosTotal, 2);

// ===============================
// ✅ Totales que se guardan en la DB
//    (tarifa base + opciones + IVA)
// ===============================
$tarifaBaseTotal = round($tarifaBase * $dias, 2);
$subtotal        = $tarifaBaseTotal + $opcionesRentaTotal;
$iva             = round($subtotal * 0.16, 2);
$total           = $subtotal + $iva;


        $codigo = 'RES-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        // 💾 Insert COMPLETO y obtener ID de la reservación
        $id = DB::table('reservaciones')->insertGetId([
            // 🔹 Cliente web (si no está logueado) → null
            'id_usuario'        => null,

            // 🔹 Asesor que crea la reserva
            'id_asesor'         => $idAsesor,

            // 🔹 Vehículo aún no asignado
            'id_vehiculo'       => null,

            'id_categoria'      => $validated['id_categoria'],

            // 🧑‍💼 Datos del cliente
            'nombre_cliente'    => $validated['nombre_cliente'],
            'apellidos_cliente' => $validated['apellidos_cliente'],
            'email_cliente'     => $validated['email_cliente'],
            'telefono_cliente'  => $validated['telefono_cliente'],

            // 📍 Ubicación
            'ciudad_retiro'     => $ciudadRetiroId,
            'ciudad_entrega'    => $ciudadEntregaId,
            'sucursal_retiro'   => $validated['sucursal_retiro'],
            'sucursal_entrega'  => $validated['sucursal_entrega'],

            // 📅 Fechas y horas
            'fecha_inicio'      => $validated['fecha_inicio'],
            'hora_retiro'       => $request->input('hora_retiro'),
            'fecha_fin'         => $validated['fecha_fin'],
            'hora_entrega'      => $request->input('hora_entrega'),

            // 💰 Tarifa base guardada en la reservación
            'tarifa_base'       => $tarifaBase,

            // 💸 Totales
            'subtotal'          => $subtotal,
            'impuestos'         => $iva,
            'total'             => $total,
            'codigo'            => $codigo,
            'estado'            => 'pendiente_pago',

            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        /* ==========================================================
           4.1️⃣ Guardar seguro seleccionado (reservacion_paquete_seguro)
        ========================================================== */
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

        /* ==========================================================
           4.2️⃣ Guardar servicios adicionales (reservacion_servicio)
        ========================================================== */
        if ($request->filled('adicionalesSeleccionados')) {
            $extras = $request->input('adicionalesSeleccionados');

            if (is_array($extras)) {
                foreach ($extras as $extra) {
                    if (!is_array($extra) || !isset($extra['id'])) {
                        continue;
                    }

                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $id,
                        'id_servicio'     => $extra['id'],
                        'cantidad'        => $extra['cantidad'] ?? 1,
                        'precio_unitario' => $extra['precio'] ?? 0,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        /* ==========================================================
           5️⃣ Enviar correo con Mailable (ReservacionAdminMail)
        ========================================================== */
        $correoCliente = $validated['email_cliente'] ?? null;
        $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');

        // Traer la reservación ya guardada para mandarla al Mailable
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        // ===============================
// ✅ Traer SEGURO (paquete) ligado a la reservación
// ===============================
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
// ✅ Imagen por categoría (referencia) — usando tu mapeo por ID
// ⚠️ Para correos: URL ABSOLUTA (APP_URL debe estar bien)
// ===============================
$catImages = [
    1  => 'img/aveo.png',
    2  => 'img/virtus.png',
    3  => 'img/jetta.png',
    4  => 'img/camry.png',
    5  => 'img/renegade.png',
    6  => 'img/seltos.png',
    7  => 'img/avanza.png',
    8  => 'img/Odyssey.png',
    9  => 'img/Hiace.png',
    10 => 'img/Frontier.png',
    11 => 'img/Tacoma.png',
];

// Id de categoría de TU BD
$catId = (int)($categoria->id_categoria ?? 0);

// Base URL (para que en correo siempre sea absoluto)
$baseUrl = rtrim(config('app.url'), '/');

// Si existe imagen para esa categoría -> úsala, si no -> placeholder
$imgPath = $catImages[$catId] ?? 'img/categorias/placeholder.png';

// URL final para el correo
$imgCategoria = $baseUrl . '/' . ltrim($imgPath, '/');

// ===============================
// ✅ Total "Opciones de renta" (seguro + servicios)
// - Seguro: precio_por_dia * dias
// - Servicios: suma (cantidad * precio_unitario)
// ===============================
$extrasServiciosTotal = 0;
if (!empty($extrasReserva)) {
    // $extrasReserva es Collection -> sum('total') funciona
    $extrasServiciosTotal = (float) $extrasReserva->sum('total');
}

$seguroTotal = 0;
if (!empty($seguroReserva) && isset($seguroReserva->precio_por_dia)) {
    $seguroTotal = (float)$seguroReserva->precio_por_dia * (float)$dias;
}

$opcionesRentaTotal = round($seguroTotal + $extrasServiciosTotal, 2);



        try {
            if ($correoCliente) {
                Mail::to($correoCliente)
    ->cc($correoEmpresa)
    ->send(new ReservacionAdminMail($reservacion,$categoria,$seguroReserva,$extrasReserva,$lugarRetiro,$lugarEntrega,$imgCategoria,$opcionesRentaTotal, $tuAuto
    ));


            } else {
                Mail::to($correoEmpresa)
    ->send(new ReservacionAdminMail($reservacion,$categoria,$seguroReserva,$extrasReserva,$lugarRetiro,$lugarEntrega,$imgCategoria,$opcionesRentaTotal, $tuAuto
    ));


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
        Log::error('❌ Error al guardar reservación: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error interno al crear la reservación.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



}
