<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratosAbiertosController extends Controller
{
    public function index()
    {
        return view('admin.AdministracionReservas');
    }

    public function api(Request $req)
{
    $size = intval($req->size ?? 10);
    $page = intval($req->page ?? 1);
    $q    = trim($req->q ?? '');

    $query = DB::table('contratos AS c')
        ->join('reservaciones AS r', 'c.id_reservacion', '=', 'r.id_reservacion')
        ->select(
            'c.id_contrato',
            'c.numero_contrato',
            'c.estado',
            'r.fecha_fin',
            'r.hora_entrega',
            'r.nombre_cliente AS nombre',
            'r.email_cliente  AS email'
        )
        ->where('c.estado', 'abierto');

    if ($q !== '') {
        $query->where(function ($w) use ($q) {
            $w->where('c.numero_contrato','LIKE',"%$q%")
              ->orWhere('r.codigo','LIKE',"%$q%")
              ->orWhere('r.nombre_cliente','LIKE',"%$q%");
        });
    }

    $total = $query->count();

    $rows = $query
        ->orderBy('c.id_contrato', 'DESC')
        ->skip(($page - 1) * $size)
        ->take($size)
        ->get();

    return response()->json([
        'data' => $rows,
        'total' => $total,
        'page' => $page,
        'last_page' => ceil($total / $size),
    ]);
}



    public function detalle($id)
{
    $ctr = DB::table('contratos AS c')
        ->join('reservaciones AS r','c.id_reservacion','=','r.id_reservacion')
        ->leftJoin('vehiculos AS v','r.id_vehiculo','=','v.id_vehiculo')
        ->leftJoin('categorias_carros AS cat','r.id_categoria','=','cat.id_categoria')
        ->select(
            'c.id_contrato',
            'c.numero_contrato',
            'c.estado',

            // 👉 lo que tu JS llama "clave"
            'r.codigo AS clave',

            'r.nombre_cliente',
            'r.email_cliente',

            // 👉 lo que tu JS espera como "telefono"
            'r.telefono_cliente AS telefono',

            // si tienes campo país en reservaciones, mapea:
            // 'r.pais_cliente AS pais',
            DB::raw('NULL AS pais'),

            // vehículo
            'v.marca',
            'v.modelo',
            'cat.nombre AS categoria',

            // 👉 mapeo para la timeline
            'r.fecha_inicio   AS entrega_fecha',
            'r.hora_retiro    AS entrega_hora',
            'r.fecha_fin      AS dev_fecha',
            'r.hora_entrega   AS dev_hora',

            // si usas delivery como lugar
            'r.delivery_direccion AS entrega_lugar',
            'r.delivery_direccion AS dev_lugar',

            // totales
            'r.total',
            'r.metodo_pago',
            'r.delivery_activo',
            'r.delivery_total',

            // si no tienes aún adicionales en DB:
            DB::raw('NULL AS adicionales')
        )
        ->where('c.id_contrato', $id)
        ->first();

    if (!$ctr) {
        return response()->json(['ok' => false], 404);
    }

    return response()->json([
        'ok'   => true,
        'data' => $ctr,
    ]);
}


}
