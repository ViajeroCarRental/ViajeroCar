<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        ->select(
            'r.*',
            'c.codigo as categoria_codigo',
            'c.nombre as categoria_nombre',
            'cr.nombre as ciudad_retiro_nombre',
            'ce.nombre as ciudad_entrega_nombre'
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

        // ✅ 4.1 Nombre del cliente
    $clienteNombre = null;

    if (!empty($reservacion->nombre_cliente)) {
        // Si la reservación ya tiene el nombre capturado, usamos ese
        $clienteNombre = $reservacion->nombre_cliente;
    } elseif (!empty($reservacion->id_usuario)) {
        // Si no, intentamos obtenerlo de la tabla usuarios
        $usuarioCliente = DB::table('usuarios')
            ->select('nombres', 'apellidos')
            ->where('id_usuario', $reservacion->id_usuario)
            ->first();

        if ($usuarioCliente) {
            $clienteNombre = trim($usuarioCliente->nombres . ' ' . $usuarioCliente->apellidos);
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


    // ✅ 5) Retornar vista
    return view('Admin.checklist', [
        'id'          => $contrato->id_contrato,
        'contrato'    => $contrato,
        'reservacion' => $reservacion,

        // 🔹 Nombres para la sección de firmas
        'clienteNombre' => $clienteNombre,
        'asesorNombre'  => $asesorNombre,

        'tipo'        => $reservacion->categoria_codigo ?? '—',
        'modelo'      => $vehiculo->modelo ?? $vehiculo->modelo_nombre ?? '—',
        'placas'      => $vehiculo->placa ?? '—',
        'color'       => $vehiculo->color ?? '—',
        'transmision' => $vehiculo->transmision ?? '—',

        'ciudadEntrega' => $reservacion->ciudad_retiro_nombre ?? '—',
        'ciudadRecibe'  => $reservacion->ciudad_entrega_nombre ?? '—',

        'kmSalida'    => $vehiculo->kilometraje ?? '—',
        'kmRegreso'   => $inspEntrada->odometro_km ?? '—',

        'proteccion'  => $reservacion->categoria_nombre ?? '—',

        // Gasolina (vehiculo.gasolina_actual es entero 0-16)
        'gasolinaSalida'  => $this->convertirEnteroAFraccion16($vehiculo->gasolina_actual ?? null),

        // OJO: inspeccion.nivel_combustible es decimal, ahorita tú guardas fracción string (eso hay que afinar)
        // Por ahora lo dejo tal cual para no romperte la UI:
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

public function enviarChecklistSalida(Request $request, $id)
{
    try {
        // 1) Validar mínimamente
        $request->validate([
    'comentario_cliente'   => 'nullable|string',
    'danos_interiores'     => 'nullable|string',
    'firma_cliente_fecha'  => 'nullable|date',
    'firma_cliente_hora'   => 'nullable|date_format:H:i',
    'entrego_fecha'        => 'nullable|date',
    'entrego_hora'         => 'nullable|date_format:H:i',
    // 👇 En salida ya NO validamos recibio_*
    'autoSalida.*'         => 'required|file|mimetypes:image/jpeg,image/png|max:2097152',
], [
    'autoSalida.*.required'  => 'Debes cargar al menos una foto de salida',
    'autoSalida.*.mimetypes' => 'Las fotos deben ser JPG o PNG',
    'autoSalida.*.max'       => 'Cada foto puede pesar como máximo 2 GB.',
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

        // 4) Inspección de SALIDA (si no existe, la creamos)
        $inspSalida = DB::table('inspeccion')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('tipo', 'salida')
            ->first();

        if ($inspSalida) {
            $idInspeccionSalida = $inspSalida->id_inspeccion;
        } else {
            // Usamos los datos actuales como referencia (km / gasolina)
            $vehiculo = null;
            if ($reservacion->id_vehiculo) {
                $vehiculo = DB::table('vehiculos')
                    ->where('id_vehiculo', $reservacion->id_vehiculo)
                    ->first();
            }

            $kmSalida = $vehiculo->kilometraje ?? 0;
            $nivelDecimal = null;

            if ($vehiculo && $vehiculo->gasolina_actual !== null) {
                // gasolina_actual es entero 0–16, lo convertimos a decimal 0.00–1.00
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
                // 👇 En salida estos se quedan siempre NULL
                'recibio_fecha'       => null,
                'recibio_hora'        => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];


        // 6) Procesar fotos de SALIDA (autoSalida[])
        $files = $request->file('autoSalida', []);

        if (!$files || !count($files)) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Debes cargar al menos una foto del vehículo (salida).'
            ], 422);
        }

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            DB::table('inspeccion_fotos_comentarios')->insert(array_merge($base, [
                'archivo'        => file_get_contents($file->getRealPath()),
                'mime_type'      => $file->getClientMimeType(),
                'nombre_archivo' => $file->getClientOriginalName(),
            ]));
        }

        return response()->json([
            'ok'  => true,
            'msg' => 'Checklist de salida guardado correctamente.'
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'ok'  => false,
            'msg' => 'Error al guardar el checklist de salida: ' . $e->getMessage()
        ], 500);
    }
}

public function enviarChecklistEntrada(Request $request, $id)
{
    try {
        // 1) Validar mínimamente
       $request->validate([
    'comentario_cliente'   => 'nullable|string',
    'danos_interiores'     => 'nullable|string',
    // 👇 En ENTRADA solo nos importan estos tiempos
    'recibio_fecha'        => 'nullable|date',
    'recibio_hora'         => 'nullable|date_format:H:i',
    'autoRegreso.*'        => 'required|file|mimetypes:image/jpeg,image/png|max:2097152',
], [
    'autoRegreso.*.required'  => 'Debes cargar al menos una foto de regreso',
    'autoRegreso.*.mimetypes' => 'Las fotos deben ser JPG o PNG',
    'autoRegreso.*.max'       => 'Cada foto puede pesar como máximo 2 GB.',
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

        // 4) Inspección de ENTRADA (si no existe, la creamos)
        $inspEntrada = DB::table('inspeccion')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('tipo', 'entrada')
            ->orderByDesc('id_inspeccion')
            ->first();

        if ($inspEntrada) {
            $idInspeccionEntrada = $inspEntrada->id_inspeccion;

            // opcional: actualizar observaciones
            DB::table('inspeccion')
                ->where('id_inspeccion', $idInspeccionEntrada)
                ->update([
                    'observaciones' => $request->input('comentario_cliente'),
                    'updated_at'    => now(),
                ]);
        } else {
            // Si no existe registro de entrada, usamos datos actuales del vehículo
            $vehiculo = null;
            if ($reservacion->id_vehiculo) {
                $vehiculo = DB::table('vehiculos')
                    ->where('id_vehiculo', $reservacion->id_vehiculo)
                    ->first();
            }

            $kmEntrada = $vehiculo->kilometraje ?? 0;
            $nivelDecimal = null;

            if ($vehiculo && $vehiculo->gasolina_actual !== null) {
                // gasolina_actual es entero 0–16, lo convertimos a decimal 0.00–1.00
                $nivelDecimal = round(((int)$vehiculo->gasolina_actual) / 16, 2);
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

        // 5) Base común para cada foto de REGRESO
        $base = [
    'id_reservacion'      => $reservacion->id_reservacion,
    'id_contrato'         => $contrato->id_contrato,
    'id_inspeccion'       => $idInspeccionEntrada,
    // 👇 DEBE SER 'entrada' porque el ENUM solo permite 'salida' o 'entrada'
    'tipo'                => 'entrada',
    'comentario_cliente'  => $request->input('comentario_cliente'),
    'danos_interiores'    => $request->input('danos_interiores'),
    // 👇 En entrada NO repetimos datos de salida
    'firma_cliente_fecha' => null,
    'firma_cliente_hora'  => null,
    'entrego_fecha'       => null,
    'entrego_hora'        => null,
    'recibio_fecha'       => $request->input('recibio_fecha') ?: null,
    'recibio_hora'        => $request->input('recibio_hora') ?: null,
    'created_at'          => now(),
    'updated_at'          => now(),
];


        // 6) Procesar fotos de REGRESO (autoRegreso[])
        $files = $request->file('autoRegreso', []);

        if (!$files || !count($files)) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Debes cargar al menos una foto del vehículo (regreso).'
            ], 422);
        }

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            DB::table('inspeccion_fotos_comentarios')->insert(array_merge($base, [
                'archivo'        => file_get_contents($file->getRealPath()),
                'mime_type'      => $file->getClientMimeType(),
                'nombre_archivo' => $file->getClientOriginalName(),
            ]));
        }

        return response()->json([
            'ok'  => true,
            'msg' => 'Checklist de regreso guardado correctamente.'
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'ok'  => false,
            'msg' => 'Error al guardar el checklist de regreso: ' . $e->getMessage()
        ], 500);
    }
}



}
