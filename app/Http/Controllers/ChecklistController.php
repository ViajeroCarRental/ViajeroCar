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
        // 1️⃣ Obtener la reservación
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
            ->where('r.id_reservacion', $id)
            ->first();

        if (!$reservacion) abort(404, "Reservación no encontrada");

        // 2️⃣ Obtener vehículo
        $vehiculo = DB::table('vehiculos')
            ->leftJoin('modelos as m', 'vehiculos.id_modelo', '=', 'm.id_modelo')
            ->select(
                'vehiculos.*',
                'm.nombre as modelo_nombre'
            )
            ->where('id_vehiculo', $reservacion->id_vehiculo)
            ->first();

        // 3️⃣ Inspección de salida
        $inspSalida = DB::table('inspeccion')
            ->where('id_contrato', $id)
            ->where('tipo', 'salida')
            ->first();

        // 4️⃣ Inspección de entrada (última registrada)
        $inspEntrada = DB::table('inspeccion')
            ->where('id_contrato', $id)
            ->where('tipo', 'entrada')
            ->orderByDesc('id_inspeccion')
            ->first();

            // Cargar el contrato asociado (si existe)
    $contrato = DB::table('contratos')
    ->where('id_reservacion', $id)
    ->first();


        // 5️⃣ Retornar datos a la vista
        return view('Admin.checklist', [
            'id'            => $id,
            'reservacion'   => $reservacion,

            'tipo'          => $reservacion->categoria_codigo,
            'modelo'        => $vehiculo->modelo ?? $vehiculo->modelo_nombre ?? '',
            'placas'        => $vehiculo->placa ?? '',
            'color'         => $vehiculo->color ?? '',
            'transmision'   => $vehiculo->transmision ?? '',

            'ciudadEntrega' => $reservacion->ciudad_retiro_nombre,
            'ciudadRecibe'  => $reservacion->ciudad_entrega_nombre,

            'kmSalida'      => $vehiculo->kilometraje,
            'kmRegreso'     => $inspEntrada->odometro_km ?? '',

            'proteccion'    => $reservacion->categoria_nombre,

            // GASOLINA
            'gasolinaSalida' => $this->convertirEnteroAFraccion16($vehiculo->gasolina_actual),
            'gasolinaRegreso' => $this->convertirEnteroAFraccion16($inspEntrada->nivel_combustible ?? null),
            'contrato' => $contrato,


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

        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!$reservacion) {
            return back()->with('error', 'Reservación no encontrada.');
        }

        DB::table('vehiculos')
            ->where('id_vehiculo', $reservacion->id_vehiculo)
            ->update([
                'kilometraje' => $request->km_regreso
            ]);

        return back()->with('success', 'Kilometraje actualizado correctamente.');
    }



    // ============================================================
    //   🟩 GUARDAR GASOLINA DE REGRESO (ACTUALIZA SI YA EXISTE)
    // ============================================================
    public function guardarGasolina(Request $req, $id)
    {
        $req->validate([
            'gasolina_regreso' => 'required'
        ]);

        // ¿Ya existe inspección de entrada?
        $existe = DB::table('inspeccion')
            ->where('id_contrato', $id)
            ->where('tipo', 'entrada')
            ->first();

        if ($existe) {
            // 🔄 Actualizar
            DB::table('inspeccion')
                ->where('id_inspeccion', $existe->id_inspeccion)
                ->update([
                    'nivel_combustible' => $req->gasolina_regreso,
                    'updated_at' => now()
                ]);
        } else {
            // ➕ Insertar nuevo registro
            DB::table('inspeccion')->insert([
                'id_contrato'        => $id,
                'tipo'               => 'entrada',
                'odometro_km'        => 0,
                'nivel_combustible'  => $req->gasolina_regreso,
                'created_at'         => now(),
                'updated_at'         => now()
            ]);
        }

        return response()->json([
            'ok'  => true,
            'msg' => 'Gasolina de regreso guardada correctamente.'
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
        ->where('evento', 'daño')
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
