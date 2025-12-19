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

    // ✅ 5) Retornar vista
    return view('Admin.checklist', [
        'id'          => $contrato->id_contrato,
        'contrato'    => $contrato,
        'reservacion' => $reservacion,

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


}
