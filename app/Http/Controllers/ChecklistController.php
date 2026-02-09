<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChecklistInspeccionMail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Log;


class ChecklistController extends Controller
{
    // ============================================================
    //   🟦 MOSTRAR CHECKLIST (RUTA PRINCIPAL)
    // ============================================================
    public function showChecklist($id)
{
    // ✅ 1) Contrato
    $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
    if (!$contrato) abort(404, "Contrato no encontrado");

    // ✅ 2) Reservación ligada al contrato
$reservacion = DB::table('reservaciones as r')
    ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
    ->leftJoin('ciudades as cr', 'r.ciudad_retiro', '=', 'cr.id_ciudad')
    ->leftJoin('ciudades as ce', 'r.ciudad_entrega', '=', 'ce.id_ciudad')
    // 👇 NUEVO: relación con el paquete de seguro
    ->leftJoin('reservacion_paquete_seguro as rps', 'r.id_reservacion', '=', 'rps.id_reservacion')
    ->leftJoin('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
    ->select(
        'r.*',
        'c.codigo as categoria_codigo',
        'c.nombre as categoria_nombre',
        'cr.nombre as ciudad_retiro_nombre',
        'ce.nombre as ciudad_entrega_nombre',
        // 👇 NUEVO: nombre del paquete de seguro
        'sp.nombre as nombre_seguro_paquete'
    )
    ->where('r.id_reservacion', $contrato->id_reservacion)
    ->first();


    if (!$reservacion) abort(404, "Reservación no encontrada");

    // ✅ 3) Vehículo (puede ser null)
    $vehiculo = null;
    if ($reservacion->id_vehiculo) {
        $vehiculo = DB::table('vehiculos')
            ->leftJoin('modelos as m', 'vehiculos.id_modelo', '=', 'm.id_modelo')
            ->select('vehiculos.*', 'm.nombre as modelo_nombre')
            ->where('vehiculos.id_vehiculo', $reservacion->id_vehiculo)
            ->first();
    }

    // ✅ 4) Inspección salida / entrada (por id_contrato)
    $inspSalida = DB::table('inspeccion')
        ->where('id_contrato', $contrato->id_contrato)
        ->where('tipo', 'salida')
        ->first();

    $inspEntrada = DB::table('inspeccion')
        ->where('id_contrato', $contrato->id_contrato)
        ->where('tipo', 'entrada')
        ->orderByDesc('id_inspeccion')
        ->first();

        // ✅ 4.1 Nombre del cliente (nombre + apellidos de la reservación)
$clienteNombre = null;

// 1) Primero usamos lo que viene directo de la tabla RESERVACIONES
if (!empty($reservacion->nombre_cliente) || !empty($reservacion->apellidos_cliente)) {
    $clienteNombre = trim(
        ($reservacion->nombre_cliente ?? '') . ' ' .
        ($reservacion->apellidos_cliente ?? '')
    );

// 2) Si por alguna razón no viene ahí, caemos al usuario ligado
} elseif (!empty($reservacion->id_usuario)) {
    $usuarioCliente = DB::table('usuarios')
        ->select('nombres', 'apellidos')
        ->where('id_usuario', $reservacion->id_usuario)
        ->first();

    if ($usuarioCliente) {
        $clienteNombre = trim(
            ($usuarioCliente->nombres ?? '') . ' ' .
            ($usuarioCliente->apellidos ?? '')
        );
    }
}


    // ✅ 4.2 Nombre del asesor / arrendador (quien hace la reservación)
    $asesorNombre = null;
    $asesorId = $contrato->id_asesor ?? $reservacion->id_asesor ?? null;

    if (!empty($asesorId)) {
        $usuarioAsesor = DB::table('usuarios')
            ->select('nombres', 'apellidos')
            ->where('id_usuario', $asesorId)
            ->first();

        if ($usuarioAsesor) {
            $asesorNombre = trim($usuarioAsesor->nombres . ' ' . $usuarioAsesor->apellidos);
        }
    }

        // ✅ 4.3 Protección y leyenda según el seguro
    $proteccionData = $this->obtenerProteccionYLeyenda($reservacion->id_reservacion);

        // ✅ 4.4 Lista de agentes que pueden RECIBIR (SuperAdmin + Ventas)
    $agentes = DB::table('usuarios as u')
        ->join('usuario_rol as ur', 'u.id_usuario', '=', 'ur.id_usuario')
        ->join('roles as r', 'ur.id_rol', '=', 'r.id_rol')
        ->where('u.activo', 1)
        ->whereIn('r.nombre', ['SuperAdmin', 'Ventas'])
        ->select(
            'u.id_usuario',
            DB::raw("CONCAT(u.nombres, ' ', u.apellidos) as nombre")
        )
        ->orderBy('nombre')
        ->get();



        // ✅ 5) Retornar vista
    return view('Admin.checklist', [
        'id'          => $contrato->id_contrato,
        'contrato'    => $contrato,
        'reservacion' => $reservacion,

        // 🔹 Nombres para la sección de firmas
        'clienteNombre' => $clienteNombre,
        'asesorNombre'  => $asesorNombre,
        'agentes'       => $agentes,

        'tipo'        => $reservacion->categoria_codigo ?? '—',
        'modelo'      => $vehiculo->modelo ?? $vehiculo->modelo_nombre ?? '—',
        'placas'      => $vehiculo->placa ?? '—',
        'color'       => $vehiculo->color ?? '—',
        'transmision' => $vehiculo->transmision ?? '—',

        'ciudadEntrega' => $reservacion->ciudad_retiro_nombre ?? '—',
        'ciudadRecibe'  => $reservacion->ciudad_entrega_nombre ?? '—',

        'kmSalida'    => $vehiculo->kilometraje ?? '—',
        'kmRegreso'   => $inspEntrada->odometro_km ?? '—',

        // 👇 Protección y leyenda dinámicas
        'proteccion'    => $proteccionData['proteccion']    // puede ser null si no hay paquete
                            ?? ($reservacion->nombre_seguro_paquete ?? '—'),
        'leyendaSeguro' => $proteccionData['leyendaSeguro'], // SIEMPRE trae algo (tiene default)

        // Gasolina (vehiculo.gasolina_actual es entero 0-16)
        'gasolinaSalida'  => $this->convertirEnteroAFraccion16($vehiculo->gasolina_actual ?? null),

        'gasolinaRegreso' => ($inspEntrada && $inspEntrada->nivel_combustible !== null)
            ? $this->convertirEnteroAFraccion16((int) round($inspEntrada->nivel_combustible * 16))
            : '',
    ]);

}

    // ============================================================
    //   🟨 ACTUALIZAR KILOMETRAJE DE REGRESO
    // ============================================================
    public function actualizarKilometraje(Request $request, $id)
{
    $request->validate([
        'km_regreso' => 'required|integer|min:0'
    ]);

    // 1) Contrato
    $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
    if (!$contrato) {
        return response()->json(['ok' => false, 'msg' => 'Contrato no encontrado'], 404);
    }

    // 2) Reservación
    $reservacion = DB::table('reservaciones')->where('id_reservacion', $contrato->id_reservacion)->first();
    if (!$reservacion || !$reservacion->id_vehiculo) {
        return response()->json(['ok' => false, 'msg' => 'Reservación o vehículo no encontrado'], 404);
    }

    // 3) Upsert inspección entrada (odometro_km)
    $existe = DB::table('inspeccion')
        ->where('id_contrato', $contrato->id_contrato)
        ->where('tipo', 'entrada')
        ->first();

    if ($existe) {
        DB::table('inspeccion')
            ->where('id_inspeccion', $existe->id_inspeccion)
            ->update([
                'odometro_km' => $request->km_regreso,
                'updated_at'  => now()
            ]);
    } else {
        DB::table('inspeccion')->insert([
            'id_contrato'   => $contrato->id_contrato,
            'tipo'          => 'entrada',
            'odometro_km'   => $request->km_regreso,
            'nivel_combustible' => null,
            'created_at'    => now(),
            'updated_at'    => now()
        ]);
    }

    // 4) Actualizar vehículo
    DB::table('vehiculos')
        ->where('id_vehiculo', $reservacion->id_vehiculo)
        ->update([
            'kilometraje' => $request->km_regreso
        ]);

    return response()->json(['ok' => true, 'msg' => 'Kilometraje de regreso guardado correctamente.']);
}




    // ============================================================
    //   🟩 GUARDAR GASOLINA DE REGRESO (ACTUALIZA SI YA EXISTE)
    // ============================================================
    public function guardarGasolina(Request $req, $id)
{
    $req->validate([
        'gasolina_regreso' => 'required|string'
    ]);

    // 1) Convertir fracción a entero (0–16)
    $entero = $this->convertirFraccion16AEntero($req->gasolina_regreso);
    if ($entero === null) {
        return response()->json([
            'ok' => false,
            'msg' => 'Nivel de gasolina inválido'
        ], 422);
    }

    // 2) Decimal para inspeccion (0.00–1.00)
    $decimal = round($entero / 16, 2);

    // 3) Contrato
    $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
    if (!$contrato) {
        return response()->json(['ok' => false, 'msg' => 'Contrato no encontrado'], 404);
    }

    // 4) Reservación y vehículo
    $reservacion = DB::table('reservaciones')
        ->where('id_reservacion', $contrato->id_reservacion)
        ->first();

    if (!$reservacion || !$reservacion->id_vehiculo) {
        return response()->json(['ok' => false, 'msg' => 'Vehículo no encontrado'], 404);
    }

    // 5) Upsert inspección de ENTRADA
    $existe = DB::table('inspeccion')
        ->where('id_contrato', $id)
        ->where('tipo', 'entrada')
        ->first();

    if ($existe) {
        DB::table('inspeccion')
            ->where('id_inspeccion', $existe->id_inspeccion)
            ->update([
                'nivel_combustible' => $decimal,
                'updated_at' => now()
            ]);
    } else {
        DB::table('inspeccion')->insert([
            'id_contrato' => $id,
            'tipo' => 'entrada',
            'odometro_km' => 0,
            'nivel_combustible' => $decimal,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // 6) Actualizar gasolina actual del vehículo
    DB::table('vehiculos')
        ->where('id_vehiculo', $reservacion->id_vehiculo)
        ->update([
            'gasolina_actual' => $entero,
            'updated_at' => now()
        ]);

    return response()->json([
        'ok' => true,
        'msg' => 'Gasolina de regreso guardada correctamente'
    ]);
}


    private function convertirEnteroAFraccion16($valor)
{
    if ($valor === null || $valor === '') return '';

    $map = [
        0  => "0",
        1  => "1/16",
        2  => "2/16",
        3  => "3/16",
        4  => "1/4",
        5  => "5/16",
        6  => "6/16",
        7  => "7/16",
        8  => "1/2",
        9  => "9/16",
        10 => "10/16",
        11 => "11/16",
        12 => "3/4",
        13 => "13/16",
        14 => "14/16",
        15 => "15/16",
        16 => "1",
    ];

    return $map[$valor] ?? '';
}

private function convertirFraccion16AEntero($valor)
{
    if ($valor === null || $valor === '') return null;

    $map = [
        "0" => 0,
        "1/16" => 1,
        "2/16" => 2,
        "3/16" => 3,
        "1/4" => 4,
        "5/16" => 5,
        "6/16" => 6,
        "7/16" => 7,
        "1/2" => 8,
        "9/16" => 9,
        "10/16" => 10,
        "11/16" => 11,
        "3/4" => 12,
        "13/16" => 13,
        "14/16" => 14,
        "15/16" => 15,
        "1" => 16,
    ];

    return $map[$valor] ?? null;
}

/**
 * Obtiene la "protección" (texto corto) y la leyenda de inspección
 * según el paquete de seguro asociado a la reservación.
 */
private function obtenerProteccionYLeyenda(int $idReservacion): array
{
    // 1) Buscar el paquete de seguro ligado a la reservación
    $paquete = DB::table('reservacion_paquete_seguro')
        ->join('seguro_paquete', 'reservacion_paquete_seguro.id_paquete', '=', 'seguro_paquete.id_paquete')
        ->where('reservacion_paquete_seguro.id_reservacion', $idReservacion)
        ->select('seguro_paquete.nombre')
        ->first();

    $proteccion    = null;
    $leyendaSeguro = null;

    if ($paquete) {
        $nombrePaquete = trim($paquete->nombre);

        // 2) Mapeo: nombre en BD -> etiqueta de protección + leyenda
        $mapa = [
            'LDW PACK' => [
                'proteccion' => 'LDW 0% Deductible',
                'leyenda'    => 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y soy responsable por el 0% deducible, de lado a lado pase lo que pase con el auto, está cubierto de bumper a bumper; salvo una negligencia.',
            ],
            'PDW PACK' => [
                'proteccion' => 'PDW 5% Deductible',
                'leyenda'    => 'Cubre toda la carrocería al 5%, 10% Pérdida total o Robo, NO CUBRE llantas, accesorios, rines ni cristales.',
            ],
            'CDW PACK 1' => [
                'proteccion' => 'CDW 10% Deductible',
                'leyenda'    => 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y soy responsable por el 10% Deducible en Daños, 20% Pérdida total o Robo sobre valor factura.',
            ],
            'CDW PACK 2' => [
                'proteccion' => 'CDW 20% Deductible',
                'leyenda'    => 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y soy responsable por el 20% Deducible en Daños, 30% Pérdida total o Robo sobre valor factura.',
            ],
            'DECLINE PROTECTIONS' => [
                'proteccion' => 'DECLINE CDW',
                'leyenda'    => 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y soy responsable por el 100% Deducible sobre valor factura de auto.',
            ],
        ];

        if (isset($mapa[$nombrePaquete])) {
            $proteccion    = $mapa[$nombrePaquete]['proteccion'];
            $leyendaSeguro = $mapa[$nombrePaquete]['leyenda'];
        }
    }

    // 3) Texto por defecto si no hay paquete o no coincide
    if ($leyendaSeguro === null) {
        $leyendaSeguro = 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y no soy responsable por daños o robo parcial o total; salvo una negligencia.';
    }

    return [
        'proteccion'    => $proteccion,
        'leyendaSeguro' => $leyendaSeguro,
    ];
}


/**
 * Devuelve los daños registrados en contrato_evento (evento = 'dano')
 * con el nombre de la zona y el comentario.
 */
private function obtenerDanosContrato(int $idContrato): array
{
    $eventos = DB::table('contrato_evento')
        ->where('id_contrato', $idContrato)
        ->where('evento', 'dano')
        ->orderBy('created_at')
        ->get();

    // Mapeo de zonas igual que en tu JS
    $mapZonas = [
        1  => "Defensa delantera",
        2  => "Defensa delantera superior",
        3  => "Costado izquierdo frontal",
        4  => "Costado derecho frontal",
        5  => "Cofre / parabrisas",
        6  => "Puerta delantera izquierda",
        7  => "Puerta delantera derecha",
        8  => "Puerta trasera izquierda",
        9  => "Puerta trasera derecha",
        10 => "Techo",
        11 => "Costado trasero izquierdo",
        12 => "Costado trasero derecho",
        13 => "Defensa trasera",
        15 => "Llanta delantera izquierda",
        16 => "Llanta delantera derecha",
        17 => "Llanta trasera izquierda",
        18 => "Llanta trasera derecha",
    ];

    $danos = [];

    foreach ($eventos as $e) {

        $detalleRaw = $e->detalle;

        // Puede venir como string JSON, array o stdClass
        if (is_string($detalleRaw)) {
            $detalle = json_decode($detalleRaw, true);
        } elseif (is_array($detalleRaw)) {
            $detalle = $detalleRaw;
        } elseif (is_object($detalleRaw)) {
            $detalle = (array) $detalleRaw;
        } else {
            $detalle = null;
        }

        if (!$detalle || !isset($detalle['zona'])) {
            continue;
        }

        $zonaId = (int) $detalle['zona'];

        $danos[] = [
            'zona'        => $zonaId,
            'nombre_zona' => $mapZonas[$zonaId] ?? ('Zona ' . $zonaId),
            'comentario'  => $detalle['comentario'] ?? '',
        ];
    }

    return $danos;
}


/**
 * Devuelve el inventario de salida "El cliente se lo lleva"
 * guardado en contrato_evento (evento = 'inventario_salida').
 */
private function obtenerInventarioSalidaContrato(int $idContrato): array
{
    $evento = DB::table('contrato_evento')
        ->where('id_contrato', $idContrato)
        ->where('evento', 'inventario_salida')
        ->orderByDesc('created_at')
        ->first();

    if (!$evento) {
        return [];
    }

    $detalle = json_decode($evento->detalle, true);
    if (!is_array($detalle)) {
        return [];
    }

    // Etiquetas bonitas para la tabla (ajusta según tus keys reales)
    $labels = [
        'placas'             => 'Placas',
        'tcirculacion'       => 'Tarjeta de circulación',
        'espejos_laterales'  => 'Espejos laterales',
        'llanta_refaccion'   => 'Llanta de refacción',
        'gato'               => 'Gato',
        'herramienta'        => 'Herramienta',
        'limpiadores'        => 'Limpiadores',
        'tapones'            => 'Tapones',
        'antena'             => 'Antena',
        // agrega aquí el resto de claves que maneja tu vista
    ];

    $items = [];

    foreach ($detalle as $clave => $valor) {
        $items[] = [
            'clave' => $clave,
            'label' => $labels[$clave] ?? ucwords(str_replace('_', ' ', $clave)),
            'valor' => (int) $valor,
        ];
    }

    return $items;
}





public function guardarDano(Request $request, $idContrato)
{
    try {
        // Validar datos mínimos
        $request->validate([
            'zona' => 'required|integer',
            'comentario' => 'nullable|string'
        ]);

        DB::table('contrato_evento')->insert([
            'id_contrato' => $idContrato,
            'evento' => 'dano',
            'detalle' => json_encode([
                'zona' => $request->zona,
                'comentario' => $request->comentario,
            ]),
            'realizado_en' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'ok' => true,
            'msg' => 'Daño registrado correctamente'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'msg' => 'Error al guardar el daño: ' . $e->getMessage()
        ], 500);
    }
}

public function listarDanos($id)
{
    $eventos = DB::table('contrato_evento')
        ->where('id_contrato', $id)
        ->where('evento', 'dano')
        ->get();

    $danos = [];

    foreach ($eventos as $e) {
        $detalle = json_decode($e->detalle);
        if ($detalle) {
            $danos[] = [
                'zona' => $detalle->zona,
                'comentario' => $detalle->comentario
            ];
        }
    }

    return response()->json(['ok' => true, 'danos' => $danos]);
}

public function guardarInventario(Request $req)
{
    try {
        DB::table('contrato_evento')->insert([
            'id_contrato' => $req->id_contrato,
            'evento'      => 'inventario_salida',
            'detalle'     => json_encode($req->inventario),
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true, 'msg' => 'Inventario guardado']);
    } catch (\Exception $e) {
        return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
    }
}

public function guardarDato(Request $request)
{
    // 1) Validar datos de entrada
    $request->validate([
        'id_contrato' => 'required|integer|exists:contratos,id_contrato',
        'campo'       => 'required|string',
        'valor'       => 'nullable|string',
    ]);

    // 2) Lista blanca de campos permitidos
    $permitidos = [
        'firma_cliente_nombre',
        'firma_cliente_fecha',
        'firma_cliente_hora',

        'entrego_nombre',
        'entrego_fecha',
        'entrego_hora',

        'recibio_nombre',   // 👈 AQUÍ entra el nuevo campo
        'recibio_fecha',
        'recibio_hora',

        'comentario_cliente',
        'danos_interiores',
    ];

    if (!in_array($request->campo, $permitidos, true)) {
        return response()->json([
            'ok'  => false,
            'msg' => 'Campo no permitido',
        ], 422);
    }

    // 3) Actualizar el contrato
    DB::table('contratos')
        ->where('id_contrato', $request->id_contrato)
        ->update([
            $request->campo => $request->valor,
            'updated_at'    => now(),
        ]);

    return response()->json(['ok' => true]);
}


public function guardarFirmaCliente(Request $req)
{
    DB::table('contratos')
        ->where('id_contrato', $req->id_contrato)
        ->update(['firma_cliente' => $req->firma]);

    return response()->json(['ok' => true]);
}

public function guardarFirmaArrendador(Request $req)
{
    DB::table('contratos')
        ->where('id_contrato', $req->id_contrato)
        ->update(['firma_arrendador' => $req->firma]);

    return response()->json(['ok' => true]);
}

public function guardarFirmaRecibio(Request $req)
{
    DB::table('contratos')
        ->where('id_contrato', $req->id_contrato)
        ->update(['firma_recibio' => $req->firma]);

    return response()->json(['ok' => true]);
}


public function enviarChecklistSalida(Request $request, $id)
{
    // 👇 Aumentar memoria solo para esta petición
    ini_set('memory_limit', '512M'); // o '256M' si quieres probar más conservador

    try {
        // 1) Validar mínimamente
        $request->validate([
            'comentario_cliente'   => 'nullable|string',
            'danos_interiores'     => 'nullable|string',
            'firma_cliente_fecha'  => 'nullable|date',
            'firma_cliente_hora'   => 'nullable|date_format:H:i',
            'entrego_fecha'        => 'nullable|date',
            'entrego_hora'         => 'nullable|date_format:H:i',

            // 🔹 Flujo viejo (sigue siendo aceptado)
            'autoSalida.*'         => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',

            // 🔹 Flujo nuevo por secciones (SALIDA)
            'frente_salida'           => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'parabrisas_salida'       => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'lado_conductor_salida'   => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'lado_pasajero_salida'    => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'atras_salida'            => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'interiores_salida.*'     => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
        ], [
            'autoSalida.*.mimetypes'      => 'Las fotos deben ser JPG o PNG',
            'autoSalida.*.max'            => 'Cada foto puede pesar como máximo 2 GB.',

            'frente_salida.mimetypes'         => 'Las fotos deben ser JPG o PNG',
            'parabrisas_salida.mimetypes'     => 'Las fotos deben ser JPG o PNG',
            'lado_conductor_salida.mimetypes' => 'Las fotos deben ser JPG o PNG',
            'lado_pasajero_salida.mimetypes'  => 'Las fotos deben ser JPG o PNG',
            'atras_salida.mimetypes'          => 'Las fotos deben ser JPG o PNG',
            'interiores_salida.*.mimetypes'   => 'Las fotos deben ser JPG o PNG',

            'frente_salida.max'         => 'Cada foto puede pesar como máximo 2 GB.',
            'parabrisas_salida.max'     => 'Cada foto puede pesar como máximo 2 GB.',
            'lado_conductor_salida.max' => 'Cada foto puede pesar como máximo 2 GB.',
            'lado_pasajero_salida.max'  => 'Cada foto puede pesar como máximo 2 GB.',
            'atras_salida.max'          => 'Cada foto puede pesar como máximo 2 GB.',
            'interiores_salida.*.max'   => 'Cada foto puede pesar como máximo 2 GB.',
        ]);

        Log::info('📋 [ChecklistSalida] Validación OK', [
            'contrato_id' => $id,
        ]);

        // 2) Buscar contrato
        $contrato = DB::table('contratos')
            ->where('id_contrato', $id)
            ->first();

        if (!$contrato) {
            Log::warning('⚠ [ChecklistSalida] Contrato no encontrado', ['id' => $id]);

            return response()->json([
                'ok'  => false,
                'msg' => 'Contrato no encontrado'
            ], 404);
        }

        // 3) Reservación ligada al contrato
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $contrato->id_reservacion)
            ->first();

        if (!$reservacion) {
            Log::warning('⚠ [ChecklistSalida] Reservación no encontrada', [
                'id_reservacion' => $contrato->id_reservacion
            ]);

            return response()->json([
                'ok'  => false,
                'msg' => 'Reservación no encontrada'
            ], 404);
        }

        Log::info('✅ [ChecklistSalida] Contrato y reservación encontrados', [
            'contrato_id'     => $contrato->id_contrato,
            'reservacion_id'  => $reservacion->id_reservacion,
            'email_cliente'   => $reservacion->email_cliente ?? null,
        ]);

        // 4) Inspección de SALIDA
        $inspSalida = DB::table('inspeccion')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('tipo', 'salida')
            ->first();

        if ($inspSalida) {
            $idInspeccionSalida = $inspSalida->id_inspeccion;
            Log::info('ℹ [ChecklistSalida] Inspección de salida existente', [
                'id_inspeccion' => $idInspeccionSalida
            ]);
        } else {
            $vehiculo = null;
            if ($reservacion->id_vehiculo) {
                $vehiculo = DB::table('vehiculos')
                    ->where('id_vehiculo', $reservacion->id_vehiculo)
                    ->first();
            }

            $kmSalida = $vehiculo->kilometraje ?? 0;
            $nivelDecimal = null;

            if ($vehiculo && $vehiculo->gasolina_actual !== null) {
                $nivelDecimal = round(((int)$vehiculo->gasolina_actual) / 16, 2);
            }

            $idInspeccionSalida = DB::table('inspeccion')->insertGetId([
                'id_contrato'       => $contrato->id_contrato,
                'tipo'              => 'salida',
                'fecha'             => now(),
                'odometro_km'       => $kmSalida,
                'nivel_combustible' => $nivelDecimal,
                'firma_cliente_url' => null,
                'observaciones'     => $request->input('comentario_cliente'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            Log::info('🆕 [ChecklistSalida] Inspección de salida creada', [
                'id_inspeccion' => $idInspeccionSalida
            ]);
        }

        // 5) Base común para cada foto
        $base = [
            'id_reservacion'      => $reservacion->id_reservacion,
            'id_contrato'         => $contrato->id_contrato,
            'id_inspeccion'       => $idInspeccionSalida,
            'tipo'                => 'salida',
            'comentario_cliente'  => $request->input('comentario_cliente'),
            'danos_interiores'    => $request->input('danos_interiores'),
            'firma_cliente_fecha' => $request->input('firma_cliente_fecha') ?: null,
            'firma_cliente_hora'  => $request->input('firma_cliente_hora') ?: null,
            'entrego_fecha'       => $request->input('entrego_fecha') ?: null,
            'entrego_hora'        => $request->input('entrego_hora') ?: null,
            'recibio_fecha'       => null,
            'recibio_hora'        => null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        // 🔧 Helper interno para insertar fotos con categoría
        $insertFoto = function ($file, ?string $categoria, ?int $interiorIndex = null) use ($base) {
            if (!$file) {
                return;
            }

            DB::table('inspeccion_fotos_comentarios')->insert(array_merge($base, [
                'foto_categoria' => $categoria,
                'interior_index' => $interiorIndex,
                'archivo'        => file_get_contents($file->getRealPath()),
                'mime_type'      => $file->getClientMimeType(),
                'nombre_archivo' => $file->getClientOriginalName(),
            ]));
        };

        // 6) Procesar fotos de SALIDA (nuevo flujo por secciones o viejo)
        $totalFotos = 0;

        $usaFlujoNuevo =
            $request->hasFile('frente_salida') ||
            $request->hasFile('parabrisas_salida') ||
            $request->hasFile('lado_conductor_salida') ||
            $request->hasFile('lado_pasajero_salida') ||
            $request->hasFile('atras_salida') ||
            $request->hasFile('interiores_salida');

        if ($usaFlujoNuevo) {
            // ✅ FLUJO NUEVO: 1 foto por sección + hasta 8 interiores

            // 1. FRENTE
            if ($file = $request->file('frente_salida')) {
                $insertFoto($file, 'frente', null);
                $totalFotos++;
            }

            // 2. PARABRISAS
            if ($file = $request->file('parabrisas_salida')) {
                $insertFoto($file, 'parabrisas', null);
                $totalFotos++;
            }

            // 3. LADO CONDUCTOR
            if ($file = $request->file('lado_conductor_salida')) {
                $insertFoto($file, 'lado_conductor', null);
                $totalFotos++;
            }

            // 4. LADO PASAJERO
            if ($file = $request->file('lado_pasajero_salida')) {
                $insertFoto($file, 'lado_pasajero', null);
                $totalFotos++;
            }

            // 5. ATRÁS
            if ($file = $request->file('atras_salida')) {
                $insertFoto($file, 'atras', null);
                $totalFotos++;
            }

            // 6. INTERIORES (máx. 8)
            $interiores = $request->file('interiores_salida', []);
            if ($interiores && !is_array($interiores)) {
                $interiores = [$interiores];
            }

            $idx = 0;
            foreach ($interiores as $file) {
                if (!$file) {
                    continue;
                }
                $idx++;
                $insertFoto($file, 'interiores', $idx);
                $totalFotos++;
            }

        } else {
            // 🔙 FLUJO VIEJO: todo viene en autoSalida[]
            $files = $request->file('autoSalida', []);

            if ($files && !is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }
                // En flujo viejo NO sabemos qué es qué → categorías NULL
                $insertFoto($file, null, null);
                $totalFotos++;
            }
        }

        if ($totalFotos === 0) {
            Log::warning('⚠ [ChecklistSalida] Sin fotos de salida en ningún flujo');

            return response()->json([
                'ok'  => false,
                'msg' => 'Debes cargar al menos una foto del vehículo (salida).'
            ], 422);
        }

        Log::info('📸 [ChecklistSalida] Fotos de salida guardadas', [
            'total_fotos' => $totalFotos,
        ]);

        // 6.1) 🔄 Traer las fotos de ESTE checklist de salida para el PDF/correo
        $fotosSalida = DB::table('inspeccion_fotos_comentarios')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('id_inspeccion', $idInspeccionSalida)
            ->where('tipo', 'salida')
            ->orderBy('id_inspeccion_fc')
            ->get();

        // Preparamos arreglo con binario, mime y nombre (si sigues mostrándolas en el mail)
        $fotosAdjuntos = $fotosSalida->map(function ($f) {
            return [
                'contenido' => $f->archivo,
                'mime'      => $f->mime_type ?: 'image/jpeg',
                'nombre'    => $f->nombre_archivo ?: ('foto-inspeccion-' . $f->id_inspeccion_fc . '.jpg'),
            ];
        })->toArray();

        // 🔹 Protección + leyenda exactamente igual que en showChecklist
        $proteccionData = $this->obtenerProteccionYLeyenda($reservacion->id_reservacion);
        $proteccion     = $proteccionData['proteccion'] ?? null;
        $leyendaSeguro  = $proteccionData['leyendaSeguro'] ?? null;

        // 🔹 Daños y inventario del contrato
        $danos      = $this->obtenerDanosContrato($contrato->id_contrato);
        $inventario = $this->obtenerInventarioSalidaContrato($contrato->id_contrato);

        // 🔹 Agrupar fotos de salida por categoría para el PDF
        $fotosSalidaPdf = [
            'frente'         => null,
            'parabrisas'     => null,
            'lado_conductor' => null,
            'lado_pasajero'  => null,
            'atras'          => null,
            'interiores'     => [],
        ];

        foreach ($fotosSalida as $f) {
            switch ($f->foto_categoria) {
                case 'frente':
                    if (!$fotosSalidaPdf['frente']) $fotosSalidaPdf['frente'] = $f;
                    break;
                case 'parabrisas':
                    if (!$fotosSalidaPdf['parabrisas']) $fotosSalidaPdf['parabrisas'] = $f;
                    break;
                case 'lado_conductor':
                    if (!$fotosSalidaPdf['lado_conductor']) $fotosSalidaPdf['lado_conductor'] = $f;
                    break;
                case 'lado_pasajero':
                    if (!$fotosSalidaPdf['lado_pasajero']) $fotosSalidaPdf['lado_pasajero'] = $f;
                    break;
                case 'atras':
                    if (!$fotosSalidaPdf['atras']) $fotosSalidaPdf['atras'] = $f;
                    break;
                case 'interiores':
                    $fotosSalidaPdf['interiores'][] = $f;
                    break;
            }
        }

        // 7) Generar PDFs y enviar correos
        $correoClienteEnviado = false;
        $correoInternoEnviado = false;

        try {
            Log::info('🧾 [ChecklistSalida] Generando PDFs para checklist salida...');

            // 🔎 Vehículo (para datos del PDF)
            $vehiculoPdf = null;
            if (!empty($reservacion->id_vehiculo)) {
                $vehiculoPdf = DB::table('vehiculos')
                    ->where('id_vehiculo', $reservacion->id_vehiculo)
                    ->first();
            }

            // ======================================================
            // ✅ 7.A) Traer datos reales capturados en el checklist (salida)
            // ======================================================
            $fcSalida = DB::table('inspeccion_fotos_comentarios')
                ->where('id_contrato', $contrato->id_contrato)
                ->where('tipo', 'salida')
                ->orderByDesc('id_inspeccion_fc') // el más reciente
                ->select([
                    'comentario_cliente',
                    'danos_interiores',
                    'firma_cliente_fecha',
                    'firma_cliente_hora',
                    'entrego_fecha',
                    'entrego_hora',
                    'recibio_fecha',
                    'recibio_hora',
                ])
                ->first();

            $comentario_cliente  = $fcSalida->comentario_cliente  ?? null;
            $danos_interiores    = $fcSalida->danos_interiores    ?? null;

            $firmaClienteFecha   = $fcSalida->firma_cliente_fecha ?? null;
            $firmaClienteHora    = $fcSalida->firma_cliente_hora  ?? null;

            $entrego_fecha       = $fcSalida->entrego_fecha ?? null;
            $entrego_hora        = $fcSalida->entrego_hora  ?? null;

            $recibio_fecha       = $fcSalida->recibio_fecha ?? null;
            $recibio_hora        = $fcSalida->recibio_hora  ?? null;

            // ======================================================
            // ✅ 7.B) Nombre del asesor
            // ======================================================
            $asesor   = '—';
            $asesorId = $contrato->id_asesor ?? null;

            if (empty($asesorId) && !empty($reservacion->id_asesor)) {
                $asesorId = $reservacion->id_asesor;
            }

            if (empty($asesorId) && session()->has('id_usuario')) {
                $asesorId = session('id_usuario');
            }

            if (!empty($asesorId)) {
                $uAsesor = DB::table('usuarios')
                    ->where('id_usuario', $asesorId)
                    ->select('nombres', 'apellidos')
                    ->first();

                if ($uAsesor) {
                    $asesor = trim(
                        ($uAsesor->nombres   ?? '') . ' ' .
                        ($uAsesor->apellidos ?? '')
                    );

                    if ($asesor === '') {
                        $asesor = '—';
                    }
                }
            }

            Log::info('🧑‍💼 [ChecklistSalida] Asesor resuelto', [
                'contrato_id'             => $contrato->id_contrato,
                'id_asesor_contrato'      => $contrato->id_asesor ?? null,
                'id_asesor_reservacion'   => $reservacion->id_asesor ?? null,
                'id_asesor_usado'         => $asesorId,
                'asesor_nombre'           => $asesor,
            ]);

            // ======================================================
            // ✅ 7.C) Nombre completo del cliente
            // ======================================================
            $nombreCliente = trim(
                ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
            );

            // ✅ GASOLINA (para mostrar en PDF)
            $gasolinaSalida = null;

            if ($vehiculoPdf && $vehiculoPdf->gasolina_actual !== null) {
                $val = (int) $vehiculoPdf->gasolina_actual;
                if ($val < 0) $val = 0;
                if ($val > 16) $val = 16;
                $gasolinaSalida = $val . '/16';
            }

            if ($gasolinaSalida === null) {
                $inspTmp = DB::table('inspeccion')
                    ->where('id_contrato', $contrato->id_contrato)
                    ->where('tipo', 'salida')
                    ->first();

                if ($inspTmp && $inspTmp->nivel_combustible !== null) {
                    $val = (int) round(((float)$inspTmp->nivel_combustible) * 16);
                    if ($val < 0) $val = 0;
                    if ($val > 16) $val = 16;

                    $gasolinaSalida = $val . '/16';
                }
            }

            $gasolinaRegreso = null;

            // Tipo vehículo = nombre de categoría
            $tipoVehiculo = null;
            $categoriaId = $vehiculoPdf->id_categoria ?? $reservacion->id_categoria ?? null;

            if (!empty($categoriaId)) {
                $tipoVehiculo = DB::table('categorias_carros')
                    ->where('id_categoria', $categoriaId)
                    ->value('nombre');
            }

            $color       = $vehiculoPdf->color ?? null;
            $transmision = $vehiculoPdf->transmision ?? null;
            $modelo      = $vehiculoPdf->modelo ?? null;
            $placas      = $vehiculoPdf->placa ?? null;

            $ciudadEntrega = DB::table('ciudades')
                ->where('id_ciudad', $reservacion->ciudad_entrega)
                ->value('nombre');

            $ciudadRecibe = DB::table('ciudades')
                ->where('id_ciudad', $reservacion->ciudad_retiro)
                ->value('nombre');

            $dataPdf = [
                'reservacion'    => $reservacion,
                'contrato'       => $contrato,
                'tipoChecklist'  => 'salida',

                'tipoVehiculo'   => $tipoVehiculo,
                'color'          => $color,
                'transmision'    => $transmision,
                'modelo'         => $modelo,
                'placas'         => $placas,
                'ciudadEntrega'  => $ciudadEntrega,
                'ciudadRecibe'   => $ciudadRecibe,

                // 👇 Protección y leyenda
                'proteccion'     => $proteccion,
                'leyendaSeguro'  => $leyendaSeguro,

                'gasolinaSalida'  => $gasolinaSalida,
                'gasolinaRegreso' => $gasolinaRegreso,

                // Comentarios checklist
                'comentario_cliente' => $comentario_cliente,
                'danos_interiores'   => $danos_interiores,

                'firmaClienteFecha'  => $firmaClienteFecha,
                'firmaClienteHora'   => $firmaClienteHora,
                'entrego_fecha'      => $entrego_fecha,
                'entrego_hora'       => $entrego_hora,
                'recibio_fecha'      => $recibio_fecha,
                'recibio_hora'       => $recibio_hora,

                'asesor'             => $asesor,
                'nombreCliente'      => $nombreCliente,

                // 👇 Daños + inventario para el diagrama y tabla
                'danos'              => $danos,
                'inventario'         => $inventario,

                // 👇 Fotos por categoría para la segunda hoja
                'fotosSalidaPdf'     => $fotosSalidaPdf,
            ];

            $pdfCliente = PDF::loadView('Admin.checklist_pdf_cliente', $dataPdf);
            $pdfInterno = PDF::loadView('Admin.checklist_pdf_interno', $dataPdf);

            Log::info('✅ [ChecklistSalida] PDFs generados correctamente');

            // Correo al cliente
            if (!empty($reservacion->email_cliente)) {
                Log::info('📧 [ChecklistSalida] Enviando checklist al CLIENTE', [
                    'email' => $reservacion->email_cliente,
                ]);

                Mail::to($reservacion->email_cliente)
                    ->send(new ChecklistInspeccionMail(
                        $reservacion,
                        $contrato,
                        'salida',
                        $pdfCliente->output(),
                        null,
                        $fotosAdjuntos
                    ));

                $correoClienteEnviado = true;

                Log::info('✅ [ChecklistSalida] Correo enviado al CLIENTE');
            } else {
                Log::warning('⚠ [ChecklistSalida] Reservación sin email_cliente, no se envía correo al cliente');
            }

            // Correo interno
            $correoInterno = config('mail.from.address', 'reservaciones@viajerocarental.com');

            Log::info('📧 [ChecklistSalida] Enviando checklist al INTERNO', [
                'email' => $correoInterno,
            ]);

            Mail::to($correoInterno)
                ->send(new ChecklistInspeccionMail(
                    $reservacion,
                    $contrato,
                    'salida',
                    $pdfInterno->output(),
                    null,
                    $fotosAdjuntos
                ));

            $correoInternoEnviado = true;

            Log::info('✅ [ChecklistSalida] Correo enviado al INTERNO');

        } catch (\Throwable $mailEx) {
            Log::error('❌ [ChecklistSalida] Error al enviar correo checklist salida', [
                'mensaje' => $mailEx->getMessage(),
                'file'    => $mailEx->getFile(),
                'line'    => $mailEx->getLine(),
            ]);
        }

        // 🎯 Mensaje final según envíos de correo
        $msg = 'Checklist de salida guardado correctamente y correos enviados.';

        if (!$correoClienteEnviado || !$correoInternoEnviado) {
            $msg = 'Checklist de salida guardado correctamente, pero hubo un problema al enviar uno o más correos. Revisa tu correo y el log.';
        }

        return response()->json([
            'ok'  => true,
            'msg' => $msg
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ [ChecklistSalida] Error general en enviarChecklistSalida', [
            'mensaje' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
            'input'   => $request->all(),
        ]);

        return response()->json([
            'ok'  => false,
            'msg' => 'Error al guardar el checklist de salida: ' . $e->getMessage()
        ], 500);
    }
}

public function enviarChecklistEntrada(Request $request, $id)
{
    // 👇 Aumentar memoria solo para esta petición
    ini_set('memory_limit', '512M'); // o '256M' si quieres probar más conservador

    try {
        // 1) Validar mínimamente
        $request->validate([
            'comentario_cliente'   => 'nullable|string',
            'danos_interiores'     => 'nullable|string',
            'recibio_fecha'        => 'nullable|date',
            'recibio_hora'         => 'nullable|date_format:H:i',

            // 🔹 Flujo viejo
            'autoRegreso.*'        => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',

            // 🔹 Flujo nuevo (REGRESO)
            'frente_regreso'           => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'parabrisas_regreso'       => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'lado_conductor_regreso'   => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'lado_pasajero_regreso'    => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'atras_regreso'            => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
            'interiores_regreso.*'     => 'nullable|file|mimetypes:image/jpeg,image/png|max:2097152',
        ], [
            'autoRegreso.*.mimetypes'      => 'Las fotos deben ser JPG o PNG',
            'autoRegreso.*.max'            => 'Cada foto puede pesar como máximo 2 GB.',

            'frente_regreso.mimetypes'         => 'Las fotos deben ser JPG o PNG',
            'parabrisas_regreso.mimetypes'     => 'Las fotos deben ser JPG o PNG',
            'lado_conductor_regreso.mimetypes' => 'Las fotos deben ser JPG o PNG',
            'lado_pasajero_regreso.mimetypes'  => 'Las fotos deben ser JPG o PNG',
            'atras_regreso.mimetypes'          => 'Las fotos deben ser JPG o PNG',
            'interiores_regreso.*.mimetypes'   => 'Las fotos deben ser JPG o PNG',

            'frente_regreso.max'         => 'Cada foto puede pesar como máximo 2 GB.',
            'parabrisas_regreso.max'     => 'Cada foto puede pesar como máximo 2 GB.',
            'lado_conductor_regreso.max' => 'Cada foto puede pesar como máximo 2 GB.',
            'lado_pasajero_regreso.max'  => 'Cada foto puede pesar como máximo 2 GB.',
            'atras_regreso.max'          => 'Cada foto puede pesar como máximo 2 GB.',
            'interiores_regreso.*.max'   => 'Cada foto puede pesar como máximo 2 GB.',
        ]);

        // 2) Buscar contrato
        $contrato = DB::table('contratos')
            ->where('id_contrato', $id)
            ->first();

        if (!$contrato) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Contrato no encontrado'
            ], 404);
        }

        // 3) Reservación ligada al contrato
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $contrato->id_reservacion)
            ->first();

        if (!$reservacion) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Reservación no encontrada'
            ], 404);
        }

        // 4) Inspección de ENTRADA
        $inspEntrada = DB::table('inspeccion')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('tipo', 'entrada')
            ->orderByDesc('id_inspeccion')
            ->first();

        if ($inspEntrada) {
            $idInspeccionEntrada = $inspEntrada->id_inspeccion;

            DB::table('inspeccion')
                ->where('id_inspeccion', $idInspeccionEntrada)
                ->update([
                    'observaciones' => $request->input('comentario_cliente'),
                    'updated_at'    => now(),
                ]);
        } else {
            $vehiculoTmp = null;
            if ($reservacion->id_vehiculo) {
                $vehiculoTmp = DB::table('vehiculos')
                    ->where('id_vehiculo', $reservacion->id_vehiculo)
                    ->first();
            }

            $kmEntrada = $vehiculoTmp->kilometraje ?? 0;
            $nivelDecimal = null;

            if ($vehiculoTmp && $vehiculoTmp->gasolina_actual !== null) {
                $nivelDecimal = round(((int)$vehiculoTmp->gasolina_actual) / 16, 2);
            }

            $idInspeccionEntrada = DB::table('inspeccion')->insertGetId([
                'id_contrato'       => $contrato->id_contrato,
                'tipo'              => 'entrada',
                'fecha'             => now(),
                'odometro_km'       => $kmEntrada,
                'nivel_combustible' => $nivelDecimal,
                'firma_cliente_url' => null,
                'observaciones'     => $request->input('comentario_cliente'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $inspEntrada = DB::table('inspeccion')
            ->where('id_inspeccion', $idInspeccionEntrada)
            ->first();

        // 5) Base común para cada foto de REGRESO
        $base = [
            'id_reservacion'      => $reservacion->id_reservacion,
            'id_contrato'         => $contrato->id_contrato,
            'id_inspeccion'       => $idInspeccionEntrada,
            'tipo'                => 'entrada',
            'comentario_cliente'  => $request->input('comentario_cliente'),
            'danos_interiores'    => $request->input('danos_interiores'),
            'firma_cliente_fecha' => null,
            'firma_cliente_hora'  => null,
            'entrego_fecha'       => null,
            'entrego_hora'        => null,
            'recibio_fecha'       => $request->input('recibio_fecha') ?: null,
            'recibio_hora'        => $request->input('recibio_hora') ?: null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        // 🔧 Helper interno para insertar fotos con categoría
        $insertFoto = function ($file, ?string $categoria, ?int $interiorIndex = null) use ($base) {
            if (!$file) {
                return;
            }

            DB::table('inspeccion_fotos_comentarios')->insert(array_merge($base, [
                'foto_categoria' => $categoria,
                'interior_index' => $interiorIndex,
                'archivo'        => file_get_contents($file->getRealPath()),
                'mime_type'      => $file->getClientMimeType(),
                'nombre_archivo' => $file->getClientOriginalName(),
            ]));
        };

        // 6) Procesar fotos de REGRESO
        $totalFotos = 0;

        $usaFlujoNuevo =
            $request->hasFile('frente_regreso') ||
            $request->hasFile('parabrisas_regreso') ||
            $request->hasFile('lado_conductor_regreso') ||
            $request->hasFile('lado_pasajero_regreso') ||
            $request->hasFile('atras_regreso') ||
            $request->hasFile('interiores_regreso');

        if ($usaFlujoNuevo) {
            // ✅ FLUJO NUEVO

            if ($file = $request->file('frente_regreso')) {
                $insertFoto($file, 'frente', null);
                $totalFotos++;
            }

            if ($file = $request->file('parabrisas_regreso')) {
                $insertFoto($file, 'parabrisas', null);
                $totalFotos++;
            }

            if ($file = $request->file('lado_conductor_regreso')) {
                $insertFoto($file, 'lado_conductor', null);
                $totalFotos++;
            }

            if ($file = $request->file('lado_pasajero_regreso')) {
                $insertFoto($file, 'lado_pasajero', null);
                $totalFotos++;
            }

            if ($file = $request->file('atras_regreso')) {
                $insertFoto($file, 'atras', null);
                $totalFotos++;
            }

            $interiores = $request->file('interiores_regreso', []);
            if ($interiores && !is_array($interiores)) {
                $interiores = [$interiores];
            }

            $idx = 0;
            foreach ($interiores as $file) {
                if (!$file) continue;
                $idx++;
                $insertFoto($file, 'interiores', $idx);
                $totalFotos++;
            }

        } else {
            // 🔙 FLUJO VIEJO: autoRegreso[]
            $files = $request->file('autoRegreso', []);
            if ($files && !is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if (!$file) continue;
                $insertFoto($file, null, null);
                $totalFotos++;
            }
        }

        if ($totalFotos === 0) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Debes cargar al menos una foto del vehículo (regreso).'
            ], 422);
        }

        // 6.1) Traer fotos de ENTRADA para adjuntarlas
        $fotosEntrada = DB::table('inspeccion_fotos_comentarios')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('id_inspeccion', $idInspeccionEntrada)
            ->where('tipo', 'entrada')
            ->orderBy('id_inspeccion_fc')
            ->get();

        $fotosAdjuntos = $fotosEntrada->map(function ($f) {
            return [
                'contenido' => $f->archivo,
                'mime'      => $f->mime_type ?: 'image/jpeg',
                'nombre'    => $f->nombre_archivo ?: ('foto-entrada-' . $f->id_inspeccion_fc . '.jpg'),
            ];
        })->toArray();// 🔹 Protección + leyenda (misma lógica)
$proteccionData = $this->obtenerProteccionYLeyenda($reservacion->id_reservacion);
$proteccion     = $proteccionData['proteccion'] ?? null;
$leyendaSeguro  = $proteccionData['leyendaSeguro'] ?? null;

// 🔹 Daños e inventario (son los mismos eventos ligados al contrato)
$danos      = $this->obtenerDanosContrato($contrato->id_contrato);
$inventario = $this->obtenerInventarioSalidaContrato($contrato->id_contrato);

// 🔹 Agrupar fotos de ENTRADA por categoría para el PDF
$fotosEntradaPdf = [
    'frente'         => null,
    'parabrisas'     => null,
    'lado_conductor' => null,
    'lado_pasajero'  => null,
    'atras'          => null,
    'interiores'     => [],
];

foreach ($fotosEntrada as $f) {
    switch ($f->foto_categoria) {
        case 'frente':
            if (!$fotosEntradaPdf['frente']) $fotosEntradaPdf['frente'] = $f;
            break;
        case 'parabrisas':
            if (!$fotosEntradaPdf['parabrisas']) $fotosEntradaPdf['parabrisas'] = $f;
            break;
        case 'lado_conductor':
            if (!$fotosEntradaPdf['lado_conductor']) $fotosEntradaPdf['lado_conductor'] = $f;
            break;
        case 'lado_pasajero':
            if (!$fotosEntradaPdf['lado_pasajero']) $fotosEntradaPdf['lado_pasajero'] = $f;
            break;
        case 'atras':
            if (!$fotosEntradaPdf['atras']) $fotosEntradaPdf['atras'] = $f;
            break;
        case 'interiores':
            $fotosEntradaPdf['interiores'][] = $f;
            break;
    }
}




        // 6.2) Datos del checklist de SALIDA ya guardados
        $fcSalida = DB::table('inspeccion_fotos_comentarios')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('tipo', 'salida')
            ->orderByDesc('id_inspeccion_fc')
            ->select([
                'comentario_cliente',
                'danos_interiores',
                'firma_cliente_fecha',
                'firma_cliente_hora',
                'entrego_fecha',
                'entrego_hora',
            ])
            ->first();

        $comentarioSalida   = $fcSalida->comentario_cliente   ?? null;
        $danosSalida        = $fcSalida->danos_interiores     ?? null;
        $firmaClienteFecha  = $fcSalida->firma_cliente_fecha  ?? null;
        $firmaClienteHora   = $fcSalida->firma_cliente_hora   ?? null;
        $entrego_fecha      = $fcSalida->entrego_fecha        ?? null;
        $entrego_hora       = $fcSalida->entrego_hora         ?? null;

        // 6.3) Datos del checklist de ENTRADA (regreso)
        $fcEntradaFoto = $fotosEntrada->last();

        $comentarioEntrada  = $fcEntradaFoto->comentario_cliente  ?? null;
        $danosEntrada       = $fcEntradaFoto->danos_interiores    ?? null;
        $recibio_fecha      = $fcEntradaFoto->recibio_fecha       ?? null;
        $recibio_hora       = $fcEntradaFoto->recibio_hora        ?? null;

        // 6.4) Resolver asesor
        $asesor   = '—';
        $asesorId = $contrato->id_asesor ?? null;

        if (empty($asesorId) && !empty($reservacion->id_asesor)) {
            $asesorId = $reservacion->id_asesor;
        }
        if (empty($asesorId) && session()->has('id_usuario')) {
            $asesorId = session('id_usuario');
        }

        if (!empty($asesorId)) {
            $uAsesor = DB::table('usuarios')
                ->where('id_usuario', $asesorId)
                ->select('nombres', 'apellidos')
                ->first();

            if ($uAsesor) {
                $asesor = trim(($uAsesor->nombres ?? '') . ' ' . ($uAsesor->apellidos ?? ''));
                if ($asesor === '') {
                    $asesor = '—';
                }
            }
        }

        $nombreCliente = trim(
            ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
        );

        // 6.5) Datos del vehículo y gasolina
        $vehiculoPdf = null;
        if (!empty($reservacion->id_vehiculo)) {
            $vehiculoPdf = DB::table('vehiculos')
                ->where('id_vehiculo', $reservacion->id_vehiculo)
                ->first();
        }

        $gasolinaSalida = null;
        $inspSalida = DB::table('inspeccion')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('tipo', 'salida')
            ->first();

        if ($inspSalida && $inspSalida->nivel_combustible !== null) {
            $val = (int) round(((float)$inspSalida->nivel_combustible) * 16);
            if ($val < 0) $val = 0;
            if ($val > 16) $val = 16;
            $gasolinaSalida = $val . '/16';
        }

        $kmSalida = $inspSalida->odometro_km ?? null;

        if ($gasolinaSalida === null && $vehiculoPdf && $vehiculoPdf->gasolina_actual !== null) {
            $val = (int) $vehiculoPdf->gasolina_actual;
            if ($val < 0) $val = 0;
            if ($val > 16) $val = 16;
            $gasolinaSalida = $val . '/16';
        }

        $gasolinaRegreso = null;
        if ($inspEntrada && $inspEntrada->nivel_combustible !== null) {
            $val = (int) round(((float)$inspEntrada->nivel_combustible) * 16);
            if ($val < 0) $val = 0;
            if ($val > 16) $val = 16;
            $gasolinaRegreso = $val . '/16';
        }

        $kmRegreso = $inspEntrada->odometro_km ?? null;

        $tipoVehiculo = null;
        $categoriaId = $vehiculoPdf->id_categoria ?? $reservacion->id_categoria ?? null;

        if (!empty($categoriaId)) {
            $tipoVehiculo = DB::table('categorias_carros')
                ->where('id_categoria', $categoriaId)
                ->value('nombre');
        }

        $color       = $vehiculoPdf->color ?? null;
        $transmision = $vehiculoPdf->transmision ?? null;
        $modelo      = $vehiculoPdf->modelo ?? null;
        $placas      = $vehiculoPdf->placa ?? null;

        $ciudadEntrega = DB::table('ciudades')
            ->where('id_ciudad', $reservacion->ciudad_entrega)
            ->value('nombre');

        $ciudadRecibe = DB::table('ciudades')
            ->where('id_ciudad', $reservacion->ciudad_retiro)
            ->value('nombre');



        // 7) Generar PDFs y enviar correos
        $correoClienteEnviado = false;
        $correoInternoEnviado = false;

        try {
            $dataPdf = [
    'reservacion'    => $reservacion,
    'contrato'       => $contrato,
    'tipoChecklist'  => 'entrada',

    'tipoVehiculo'   => $tipoVehiculo,
    'tipo'           => $tipoVehiculo,
    'color'          => $color,
    'transmision'    => $transmision,
    'modelo'         => $modelo,
    'placas'         => $placas,
    'ciudadEntrega'  => $ciudadEntrega,
    'ciudadRecibe'   => $ciudadRecibe,

    // 👇 Protección y leyenda
    'proteccion'     => $proteccion,
    'leyendaSeguro'  => $leyendaSeguro,

    'gasolinaSalida'  => $gasolinaSalida,
    'gasolinaRegreso' => $gasolinaRegreso,

    'kmSalida'        => $kmSalida,
    'kmRegreso'       => $kmRegreso,

    // Comentarios (preferimos lo de ENTRADA, si no hay usamos SALIDA)
    'comentario_cliente' => $comentarioEntrada ?? $comentarioSalida,
    'danos_interiores'   => $danosEntrada ?? $danosSalida,

    'comentarioCliente'  => $comentarioEntrada ?? $comentarioSalida,
    'danosInteriores'    => $danosEntrada ?? $danosSalida,

    'firmaClienteFecha'  => $firmaClienteFecha,
    'firmaClienteHora'   => $firmaClienteHora,
    'entrego_fecha'      => $entrego_fecha,
    'entrego_hora'       => $entrego_hora,
    'recibio_fecha'      => $recibio_fecha,
    'recibio_hora'       => $recibio_hora,

    'entregoFecha'       => $entrego_fecha,
    'entregoHora'        => $entrego_hora,
    'recibioFecha'       => $recibio_fecha,
    'recibioHora'        => $recibio_hora,

    'asesor'             => $asesor,
    'nombreCliente'      => $nombreCliente,

    'clienteNombre'      => $nombreCliente,
    'asesorNombre'       => $asesor,
    'entregoNombre'      => $asesor,
    'recibioNombre'      => $contrato->recibio_nombre ?: $asesor,

    // 👇 Daños + inventario
    'danos'              => $danos,
    'inventario'         => $inventario,

    // 👇 Fotos por categoría para segunda hoja
    'fotosEntradaPdf'    => $fotosEntradaPdf,
];


            $pdfCliente = PDF::loadView('Admin.checklist_pdf_cliente', $dataPdf);
            $pdfInterno = PDF::loadView('Admin.checklist_pdf_interno', $dataPdf);

            if (!empty($reservacion->email_cliente)) {
                Mail::to($reservacion->email_cliente)
                    ->send(new ChecklistInspeccionMail(
                        $reservacion,
                        $contrato,
                        'entrada',
                        $pdfCliente->output(),
                        null,
                        $fotosAdjuntos
                    ));

                $correoClienteEnviado = true;
            }

            $correoInterno = config('mail.from.address', 'reservaciones@viajerocarental.com');

            Mail::to($correoInterno)
                ->send(new ChecklistInspeccionMail(
                    $reservacion,
                    $contrato,
                    'entrada',
                    $pdfCliente->output(),
                    $pdfInterno->output(),
                    $fotosAdjuntos
                ));

            $correoInternoEnviado = true;

        } catch (\Throwable $mailEx) {
            Log::error('Error al enviar correo checklist entrada: '.$mailEx->getMessage(), [
                'file' => $mailEx->getFile(),
                'line' => $mailEx->getLine(),
            ]);
        }

        // 🎯 Mensaje final según envío de correos
        $msg = 'Checklist de regreso guardado correctamente y correos enviados.';

        if (!$correoClienteEnviado || !$correoInternoEnviado) {
            $msg = 'Checklist de regreso guardado correctamente, pero hubo un problema al enviar uno o más correos. Revisa tu correo y el log.';
        }

        return response()->json([
           'ok'  => true,
           'msg' => $msg
        ]);

    } catch (\Throwable $e) {
        Log::error('Error general en enviarChecklistEntrada: '.$e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'ok'  => false,
            'msg' => 'Error al guardar el checklist de regreso: ' . $e->getMessage()
        ], 500);
    }
}


}
