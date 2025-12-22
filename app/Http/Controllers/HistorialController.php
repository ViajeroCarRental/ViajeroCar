<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistorialController extends Controller
{
    public function index()
    {
        return view('Admin.Historial');
    }

    public function api(Request $req)
    {
        $q      = $req->q ?? '';
        $fini   = $req->fini ?? null;
        $ffin   = $req->ffin ?? null;
        $pp     = intval($req->pp ?? 20);

        // ✅ nuevos filtros que manda el JS
        $fstatus   = $req->fstatus ?? '';
        $fpago     = $req->fpago ?? '';
        $fsucursal = $req->fsucursal ?? '';
        $fvehiculo = $req->fvehiculo ?? '';

        // ============================================================
        // 1) COTIZACIONES
        // ============================================================
        $cot = DB::table('cotizaciones')
            ->select(
                'id_cotizacion',
                'folio',
                'cliente',
                'categoria_nombre',
                'vehiculo_marca',
                'vehiculo_modelo',
                'pickup_date',
                'pickup_name',
                'dropoff_name',
                'days',
                'total'
            );

        if ($q) {
            $cot->where(function ($w) use ($q) {
                $w->where('folio', 'LIKE', "%$q%")
                  ->orWhere('categoria_nombre', 'LIKE', "%$q%")
                  ->orWhere('vehiculo_modelo', 'LIKE', "%$q%")
                  ->orWhere('vehiculo_marca', 'LIKE', "%$q%");
            });
        }

        if ($fini) $cot->where('pickup_date', '>=', $fini);
        if ($ffin) $cot->where('pickup_date', '<=', $ffin);

        // filtros aplicables a cotizaciones
        if ($fsucursal) $cot->where('pickup_name', $fsucursal);
        if ($fvehiculo) $cot->where('categoria_nombre', $fvehiculo);

        // si el filtro de estatus NO es "Cotización", no deben salir cotizaciones
        if ($fstatus && mb_strtolower($fstatus) !== 'cotización') {
            $cot->whereRaw('1=0');
        }

        $cot = $cot->get()->map(function ($c) {
            $cli = $c->cliente ? json_decode($c->cliente, true) : [];

            $vehiculo = $c->vehiculo_modelo
                ? "{$c->vehiculo_marca} {$c->vehiculo_modelo}"
                : $c->categoria_nombre;

            return [
                'tipo'     => 'cotizacion',
                'folio'    => $c->folio,
                'fecha'    => $c->pickup_date,
                'cliente'  => $cli['nombre'] ?? 'Cliente',
                'vehiculo' => $vehiculo,
                'dias'     => $c->days,
                'sucursal' => $c->pickup_name ?? 'N/A',
                'estatus'  => 'Cotización',
                'total'    => $c->total,
                'pagado'   => null,
                'saldo'    => null,
            ];
        });

        // ============================================================
        // 2) RESERVACIONES
        // ============================================================
        $res = DB::table('reservaciones AS r')
            ->leftJoin('usuarios AS u', 'r.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('categorias_carros AS c', 'r.id_categoria', '=', 'c.id_categoria')
            ->leftJoin('vehiculos AS v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
            ->leftJoin('sucursales AS s', 'r.sucursal_retiro', '=', 's.id_sucursal')
            ->select(
                'r.codigo',
                'r.fecha_inicio',
                'r.fecha_fin',
                'r.total',
                'r.status_pago',
                'r.estado', // ✅ IMPORTANTE (cancelada, no_show, confirmada, etc.)
                'r.nombre_cliente',
                'r.telefono_cliente',
                'u.nombres',
                'u.apellidos',
                'c.nombre AS categoria',
                'v.modelo',
                'v.marca',
                's.nombre AS sucursal'
            );

        if ($q) {
            $res->where(function ($w) use ($q) {
                $w->where('r.codigo', 'LIKE', "%$q%")
                  ->orWhere('r.nombre_cliente', 'LIKE', "%$q%")
                  ->orWhere('u.nombres', 'LIKE', "%$q%")
                  ->orWhere('u.apellidos', 'LIKE', "%$q%");
            });
        }

        if ($fini) $res->where('r.fecha_inicio', '>=', $fini);
        if ($ffin) $res->where('r.fecha_inicio', '<=', $ffin);

        // ✅ filtros extra (reservaciones)
        if ($fpago) $res->where('r.status_pago', $fpago);
        if ($fsucursal) $res->where('s.nombre', $fsucursal);
        if ($fvehiculo) $res->where('c.nombre', $fvehiculo);

        // ✅ filtro de estatus del select (texto del blade) -> valor DB (estado)
        if ($fstatus) {
            $map = [
                'Cancelada' => 'cancelada',
                'No show'   => 'no_show',
                'Reservada' => 'confirmada',
            ];
            $estadoDb = $map[$fstatus] ?? $fstatus; // si algún día mandas el valor directo
            $res->where('r.estado', $estadoDb);
        }

        $res = $res->get()->map(function ($r) {

            $cliente = $r->nombres
                ? "{$r->nombres} {$r->apellidos}"
                : $r->nombre_cliente;

            $vehiculo = $r->modelo
                ? "{$r->marca} {$r->modelo}"
                : $r->categoria;

            $dias = Carbon::parse($r->fecha_inicio)->diffInDays($r->fecha_fin);
            if ($dias == 0) $dias = 1;

            $pagado = $r->status_pago === 'Pagado' ? $r->total : 0;
            $saldo  = $r->total - $pagado;

            // ✅ etiqueta bonita para mostrar
            $estado = $r->estado ?? 'confirmada';
            $label = match ($estado) {
                'no_show'        => 'No show',
                'cancelada'      => 'Cancelada',
                'expirada'       => 'Expirada',
                'confirmada'     => 'Reservada',
                'pendiente_pago' => 'Reservada',
                'hold'           => 'Reservada',
                default          => ucfirst(str_replace('_',' ', $estado)),
            };

            return [
                'tipo'     => 'reservacion',
                'folio'    => $r->codigo,
                'fecha'    => $r->fecha_inicio,
                'cliente'  => $cliente,
                'vehiculo' => $vehiculo,
                'dias'     => $dias,
                'sucursal' => $r->sucursal ?? '—',
                'estatus'  => $label, // ✅ YA NO ES status_pago
                'total'    => $r->total,
                'pagado'   => $pagado,
                'saldo'    => $saldo,
            ];
        });

        // ============================================================
        // 3) CONTRATOS
        // ============================================================
        $ctr = DB::table('contratos AS c')
            ->join('reservaciones AS r', 'c.id_reservacion', '=', 'r.id_reservacion')
            ->leftJoin('usuarios AS u', 'r.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('categorias_carros AS cat', 'r.id_categoria', '=', 'cat.id_categoria')
            ->leftJoin('vehiculos AS v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
            ->leftJoin('sucursales AS s', 'r.sucursal_retiro', '=', 's.id_sucursal')
            ->select(
                'c.numero_contrato',
                'c.abierto_en',
                'c.estado AS status_contrato',
                'r.total',
                'r.status_pago',
                'r.fecha_inicio',
                'r.fecha_fin',
                'r.nombre_cliente',
                'u.nombres',
                'u.apellidos',
                'cat.nombre AS categoria',
                'v.marca',
                'v.modelo',
                's.nombre AS sucursal'
            );

        if ($q) {
            $ctr->where(function ($w) use ($q) {
                $w->where('c.numero_contrato', 'LIKE', "%$q%")
                  ->orWhere('u.nombres', 'LIKE', "%$q%")
                  ->orWhere('u.apellidos', 'LIKE', "%$q%")
                  ->orWhere('r.nombre_cliente', 'LIKE', "%$q%");
            });
        }

        if ($fini) $ctr->where('c.abierto_en', '>=', $fini);
        if ($ffin) $ctr->where('c.abierto_en', '<=', $ffin);

        // ✅ filtros extra (contratos)
        if ($fpago) $ctr->where('r.status_pago', $fpago);
        if ($fsucursal) $ctr->where('s.nombre', $fsucursal);
        if ($fvehiculo) $ctr->where('cat.nombre', $fvehiculo);
        // fstatus en contratos depende tus valores reales; si quieres, lo ajustamos luego

        $ctr = $ctr->get()->map(function ($c) {

            $cliente = $c->nombres
                ? "{$c->nombres} {$c->apellidos}"
                : $c->nombre_cliente;

            $vehiculo = $c->modelo
                ? "{$c->marca} {$c->modelo}"
                : $c->categoria;

            $dias = Carbon::parse($c->fecha_inicio)->diffInDays($c->fecha_fin);
            if ($dias == 0) $dias = 1;

            $pagado = $c->status_pago === 'Pagado' ? $c->total : 0;
            $saldo  = $c->total - $pagado;

            return [
                'tipo'     => 'contrato',
                'folio'    => $c->numero_contrato,
                'fecha'    => $c->abierto_en,
                'cliente'  => $cliente,
                'vehiculo' => $vehiculo,
                'dias'     => $dias,
                'sucursal' => $c->sucursal ?? '—',
                'estatus'  => $c->status_contrato,
                'total'    => $c->total,
                'pagado'   => $pagado,
                'saldo'    => $saldo,
            ];
        });

        // ============================================================
        // MERGE + ORDENAR + PAGINAR
        // ============================================================
        $final = $cot
            ->merge($res)
            ->merge($ctr)
            ->sortByDesc('fecha')
            ->values();

        $total = $final->count();

        $page = $req->page ?? 1;
        $chunks = $final->forPage($page, $pp)->values();

        return response()->json([
            'total'        => $total,
            'data'         => $chunks,
            'current_page' => $page,
            'last_page'    => ceil($total / $pp)
        ]);
    }
}
