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
        ->leftJoin('reservaciones AS r','c.id_reservacion','=','r.id_reservacion')
        ->leftJoin('vehiculos AS v','r.id_vehiculo','=','v.id_vehiculo')
        ->leftJoin('categorias_carros AS cat','r.id_categoria','=','cat.id_categoria')
        ->select(
            'c.id_contrato',
            'c.numero_contrato',
            'c.estado',
            'r.id_reservacion',

            DB::raw('r.codigo AS clave'),
            DB::raw('r.nombre_cliente AS nombre_cliente'),
            DB::raw('r.email_cliente AS email_cliente'),
            DB::raw('r.telefono_cliente AS telefono'),

            DB::raw('NULL AS pais'),

            'v.marca',
            'v.modelo',
            'cat.nombre AS categoria',

            DB::raw('r.fecha_inicio AS entrega_fecha'),
            DB::raw('r.hora_retiro AS entrega_hora'),
            DB::raw('r.fecha_fin AS dev_fecha'),
            DB::raw('r.hora_entrega AS dev_hora'),

            DB::raw('r.delivery_direccion AS entrega_lugar'),
            DB::raw('r.delivery_direccion AS dev_lugar'),

            'r.total',
            'r.metodo_pago',
            'r.delivery_activo',
            'r.delivery_total',

            DB::raw('NULL AS adicionales')
        )
        ->where('c.id_contrato', $id)
        ->first();

    if (!$ctr) {
        return response()->json(['ok' => false], 404);
    }

    return response()->json([
        'ok' => true,
        'data' => $ctr,
    ]);
}



}
