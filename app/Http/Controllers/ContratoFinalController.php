<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Mail\ContratoFinalMail;

class ContratoFinalController extends Controller
{
    /* =========================================================
       MOSTRAR CONTRATO EN PANTALLA
    ========================================================= */
    public function mostrarContratoFinal($idContrato)
    {
        // 1️⃣ Contrato
        $contrato = DB::table('contratos')
            ->where('id_contrato', $idContrato)
            ->first();

        if (!$contrato) {
            return back()->with('error', 'Contrato no encontrado.');
        }

        // 2️⃣ Reservación
        $reservacion = DB::table('reservaciones as r')
            ->leftJoin('sucursales as sr', 'r.sucursal_retiro', '=', 'sr.id_sucursal')
            ->leftJoin('sucursales as se', 'r.sucursal_entrega', '=', 'se.id_sucursal')
            ->select(
                'r.*',
                'sr.nombre as sucursal_retiro_nombre',
                'se.nombre as sucursal_entrega_nombre'
            )
            ->where('r.id_reservacion', $contrato->id_reservacion)
            ->first();

        if (!$reservacion) {
            return back()->with('error', 'Reservación no encontrada.');
        }

        // 3️⃣ Licencia
        $licencia = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->where('tipo', 'licencia')
            ->first();

        // 4️⃣ Días
        $dias = max(
            \Carbon\Carbon::parse($reservacion->fecha_inicio)
                ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin)) + 1,
            1
        );

        // 5️⃣ Tarifas
        $tarifaBase = $reservacion->tarifa_modificada ?? $reservacion->tarifa_base ?? 0;

        $paquetes = DB::table('reservacion_paquete_seguro as rps')
            ->leftJoin('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
            ->select('sp.nombre', 'rps.precio_por_dia')
            ->where('rps.id_reservacion', $reservacion->id_reservacion)
            ->get();

        $individuales = DB::table('reservacion_seguro_individual as rsi')
            ->leftJoin('seguro_individuales as si', 'rsi.id_individual', '=', 'si.id_individual')
            ->select('si.nombre', 'rsi.precio_por_dia')
            ->where('rsi.id_reservacion', $reservacion->id_reservacion)
            ->get();

        $extras = DB::table('reservacion_servicio as rs')
            ->leftJoin('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
            ->select('s.nombre', 'rs.precio_unitario')
            ->where('rs.id_reservacion', $reservacion->id_reservacion)
            ->get();

        // 6️⃣ Totales
        $subtotal =
            ($tarifaBase * $dias) +
            $paquetes->sum(fn($p) => $p->precio_por_dia * $dias) +
            $individuales->sum(fn($i) => $i->precio_por_dia * $dias) +
            $extras->sum(fn($e) => $e->precio_unitario * $dias);

        $totalFinal = $subtotal * 1.16;

        // 7️⃣ Vehículo
        $vehiculo = DB::table('vehiculos as v')
            ->leftJoin('categorias_carros as c', 'v.id_categoria', '=', 'c.id_categoria')
            ->select(
                'v.modelo',
                'v.color',
                'v.transmision',
                'v.kilometraje',
                'v.gasolina_actual',
                DB::raw('COALESCE(c.nombre, v.categoria) as categoria')
            )
            ->where('v.id_vehiculo', $reservacion->id_vehiculo)
            ->first();

        return view('Admin.ContratoFinal', compact(
            'contrato',
            'reservacion',
            'licencia',
            'vehiculo',
            'dias',
            'tarifaBase',
            'paquetes',
            'individuales',
            'extras',
            'subtotal',
            'totalFinal'
        ));
    }

    /* =========================================================
       GUARDAR FIRMAS
    ========================================================= */
    public function guardarFirmaCliente(Request $request)
    {
        DB::table('contratos')
            ->where('id_contrato', $request->id_contrato)
            ->update(['firma_cliente' => $request->firma]);

        return response()->json(['ok' => true]);
    }

    public function guardarFirmaArr(Request $request)
    {
        DB::table('contratos')
            ->where('id_contrato', $request->id_contrato)
            ->update(['firma_arrendador' => $request->firma]);

        return response()->json(['ok' => true]);
    }

    /* =========================================================
       ENVIAR CONTRATO POR CORREO (PDF)
    ========================================================= */
    public function enviarContratoCorreo(Request $request, $id)
    {
        $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
        if (!$contrato) {
            return response()->json(['ok' => false, 'msg' => 'Contrato no encontrado']);
        }

        $reservacion = DB::table('reservaciones')->where('id_reservacion', $contrato->id_reservacion)->first();
        if (!$reservacion || !$reservacion->email_cliente) {
            return response()->json(['ok' => false, 'msg' => 'Correo no disponible']);
        }

        $aviso = $request->input('aviso', '');

        // Reusar datos
        $dias = max(
            \Carbon\Carbon::parse($reservacion->fecha_inicio)
                ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin)) + 1,
            1
        );

        $tarifaBase = $reservacion->tarifa_modificada ?? $reservacion->tarifa_base ?? 0;
        $subtotal = $tarifaBase * $dias;
        $totalFinal = $subtotal * 1.16;

        // PDF
        $pdf = Pdf::loadView('Admin.contrato-final-pdf', compact(
            'contrato',
            'reservacion',
            'dias',
            'tarifaBase',
            'subtotal',
            'totalFinal'
        ))
        ->setPaper('legal', 'portrait')
        ->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $filePath = storage_path("app/public/Contrato_{$id}.pdf");
        $pdf->save($filePath);

        Mail::to($reservacion->email_cliente)->send(
            new ContratoFinalMail(
                $contrato,
                $reservacion,
                null,
                null,
                $dias,
                $totalFinal,
                $filePath,
                $aviso
            )
        );

        return response()->json([
            'ok' => true,
            'msg' => 'Contrato enviado correctamente'
        ]);
    }
}
