<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\CierreContratoMail;

class ContratosAbiertosController extends Controller
{
    public function index()
    {
        return view('Admin.AdministracionReservas');
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
    'r.nombre_cliente   AS nombre',
    'r.apellidos_cliente AS apellidos',
    'r.email_cliente    AS email'
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

public function saldo($idContrato)
{
    // 1) Buscar contrato
    $contrato = DB::table('contratos')
        ->where('id_contrato', $idContrato)
        ->first();

    if (!$contrato) {
        return response()->json([
            'ok' => false,
            'msg' => 'Contrato no encontrado'
        ]);
    }

    // 2) Buscar reservación asociada
    $res = DB::table('reservaciones')
        ->where('id_reservacion', $contrato->id_reservacion)
        ->first();

    if (!$res) {
        return response()->json([
            'ok' => false,
            'msg' => 'Reservación no encontrada'
        ]);
    }

    // 3) Obtener total del contrato (reservación)
    $total = floatval($res->total);

    // 4) Sumar pagos con estatus "paid"
    $pagado = DB::table('pagos')
        ->where('id_reservacion', $res->id_reservacion)
        ->where('estatus', 'paid')
        ->sum('monto');

    $saldo = $total - $pagado;

    return response()->json([
        'ok'      => true,
        'total'   => round($total, 2),
        'pagado'  => round($pagado, 2),
        'saldo'   => round($saldo, 2)
    ]);
}

public function finalizarContrato($id)
{
    try {

        // 1) Obtener contrato
        $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
        if (!$contrato) {
            return response()->json(['ok' => false, 'msg' => 'Contrato no encontrado']);
        }

        // 2) Obtener reservación asociada
        $reservacion = DB::table('reservaciones')->where('id_reservacion', $contrato->id_reservacion)->first();
        if (!$reservacion) {
            return response()->json(['ok' => false, 'msg' => 'Reservación no encontrada']);
        }

        // 3) Obtener vehículo
        $vehiculo = DB::table('vehiculos')
            ->where('id_vehiculo', $reservacion->id_vehiculo)
            ->first();

        if (!$vehiculo) {
            return response()->json(['ok' => false, 'msg' => 'Vehículo no encontrado']);
        }

        // 4) Validar correo del cliente
        if (empty($reservacion->email_cliente)) {
            return response()->json(['ok' => false, 'msg' => 'El cliente no tiene correo registrado']);
        }

        // 5) Pagos
        $pagos = DB::table('pagos')
            ->where('id_reservacion', $reservacion->id_reservacion)
            ->orWhere('id_contrato', $id)
            ->get();

        // 6) Servicios
        $servicios = DB::table('reservacion_servicio as rs')
            ->leftJoin('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
            ->where('rs.id_reservacion', $reservacion->id_reservacion)
            ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario')
            ->get();

        // 7) Seguros Paquete
        $segurosPaquete = DB::table('reservacion_paquete_seguro as rps')
            ->leftJoin('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
            ->where('rps.id_reservacion', $reservacion->id_reservacion)
            ->select('sp.nombre', 'rps.precio_por_dia')
            ->get();

        // 8) Seguros Individuales
        $segurosIndividuales = DB::table('reservacion_seguro_individual as rsi')
            ->leftJoin('seguro_individuales as si', 'rsi.id_individual', '=', 'si.id_individual')
            ->where('rsi.id_reservacion', $reservacion->id_reservacion)
            ->select('si.nombre', 'rsi.precio_por_dia', 'rsi.cantidad')
            ->get();

        // 9) Cargos adicionales
        $cargos = DB::table('cargo_adicional')
            ->where('id_contrato', $id)
            ->get();

        // 10) Calcular días de renta
        $dias = \Carbon\Carbon::parse($reservacion->fecha_inicio)
            ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin));

        // 11) Enviar correo con ambos PDFs adjuntos
        Mail::to($reservacion->email_cliente)
            ->send(new CierreContratoMail(
                $reservacion,
                $contrato,
                $pagos,
                $servicios,
                $segurosPaquete,
                $segurosIndividuales,
                $cargos,
                $dias,
                $vehiculo
            ));

        return response()->json([
            'ok' => true,
            'msg' => "Correo enviado a {$reservacion->email_cliente}"
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'ok' => false,
            'msg' => $e->getMessage()
        ]);
    }
}





}
