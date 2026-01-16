<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChecklistCambioAutoController extends Controller
{
    /**
     * Mostrar checklist de cambio de auto
     * $id = id_contrato
     */
    public function index($id)
    {
        // 1) Contrato
        $contrato = DB::table('contratos')
            ->where('id_contrato', $id)
            ->first();

        if (!$contrato) {
            abort(404, 'Contrato no encontrado.');
        }

        // 2) Reservación ligada al contrato
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $contrato->id_reservacion)
            ->first();

        if (!$reservacion) {
            abort(404, 'Reservación no encontrada para este contrato.');
        }

        // 3) Vehículo original de la reservación
        $vehiculo = null;
        if (!empty($reservacion->id_vehiculo)) {
            $vehiculo = DB::table('vehiculos')
                ->where('id_vehiculo', $reservacion->id_vehiculo)
                ->first();
        }

        // 4) Categoría original (para mostrar el CÓDIGO)
        $categoria = null;
        if (!empty($reservacion->id_categoria)) {
            $categoria = DB::table('categorias_carros')
                ->where('id_categoria', $reservacion->id_categoria)
                ->first();
        }

        // 5) Enviamos todo a la vista
        return view('Admin.checklist2', [
            'contrato'    => $contrato,
            'reservacion' => $reservacion,
            'vehiculo'    => $vehiculo,
            'categoria'   => $categoria,
        ]);
    }


}
