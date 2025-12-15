<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisorReservacionesController extends Controller
{
    public function index()
    {
        return view('Admin.visorReservaciones');
    }

    // 📌 API para obtener reservaciones filtradas + paginación
    public function api(Request $req)
    {
        $q = $req->q ?? '';
        $pp = intval($req->pp ?? 25);

        $query = DB::table('reservaciones AS r')
            ->leftJoin('usuarios AS u', 'r.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('categorias_carros AS c', 'r.id_categoria', '=', 'c.id_categoria')
            ->select(
                'r.id_reservacion',
                'r.codigo',
                'r.fecha_fin',
                'r.hora_entrega',
                'r.fecha_inicio',
                'r.fecha_fin',
                DB::raw("c.nombre AS categoria"),
                DB::raw("COALESCE(u.nombres, r.nombre_cliente) AS nombre"),
                DB::raw("COALESCE(u.numero, r.telefono_cliente) AS telefono")
            );

        // 🔎 Búsqueda general
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('r.codigo', 'LIKE', "%$q%")
                    ->orWhere('r.nombre_cliente', 'LIKE', "%$q%")
                    ->orWhere('u.nombres', 'LIKE', "%$q%")
                    ->orWhere('u.apellidos', 'LIKE', "%$q%")
                    ->orWhere('r.telefono_cliente', 'LIKE', "%$q%")
                    ->orWhere('u.numero', 'LIKE', "%$q%");
            });
        }

        // ⏳ Paginación
        $total = $query->count();
        $rows = $query->orderBy('r.fecha_fin', 'desc')->paginate($pp);

        // Convertimos días
        $rows->getCollection()->transform(function ($r) {
            $dias = Carbon::parse($r->fecha_inicio)->diffInDays(Carbon::parse($r->fecha_fin));
            if ($dias == 0) $dias = 1;
            $r->dias = $dias;
            return $r;
        });

        return response()->json([
            'total' => $total,
            'data' => $rows->items(),
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage()
        ]);
    }
}
