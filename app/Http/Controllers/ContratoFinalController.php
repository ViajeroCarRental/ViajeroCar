<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContratoFinalMail;   // ← ESTE FALTABA


class ContratoFinalController extends Controller
{
    public function mostrarContratoFinal($idContrato)
{
    // ============================
    // 1️⃣ Obtener contrato
    // ============================
    $contrato = DB::table('contratos')
        ->where('id_contrato', $idContrato)
        ->first();

    if (!$contrato) {
        return redirect()->back()->with('error', 'Contrato no encontrado.');
    }

    // ============================
    // 2️⃣ Obtener reservación asociada
    // ============================
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
        return redirect()->back()->with('error', 'Reservación no encontrada.');
    }

    // ============================
    // 3️⃣ Obtener licencia del titular
    // ============================
    $licencia = DB::table('contrato_documento')
        ->where('id_contrato', $idContrato)
        ->where('tipo', 'licencia')
        ->whereNull('id_conductor')
        ->first();

    if (!$licencia) {
        $licencia = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->where('tipo', 'licencia')
            ->first();
    }

    // ============================
    // 4️⃣ Calcular días de renta
    // ============================
    $dias = \Carbon\Carbon::parse($reservacion->fecha_inicio)
        ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin));

if ($dias == 0) $dias = 1; // Se cobra día completo
$dias = $dias + 1;

    // ============================
    // 5️⃣ Tarifas y protecciones
    // ============================

    // Tarifa base
    $tarifaBase = $reservacion->tarifa_modificada ?? $reservacion->tarifa_base ?? 0;

    // Paquetes de seguro
    $paquetes = DB::table('reservacion_paquete_seguro as rps')
        ->leftJoin('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
        ->select('sp.nombre', 'rps.precio_por_dia')
        ->where('rps.id_reservacion', $reservacion->id_reservacion)
        ->get();

    // Seguros individuales
    $individuales = DB::table('reservacion_seguro_individual as rsi')
        ->leftJoin('seguro_individuales as si', 'rsi.id_individual', '=', 'si.id_individual')
        ->select('si.nombre', 'rsi.precio_por_dia')
        ->where('rsi.id_reservacion', $reservacion->id_reservacion)
        ->get();

    // Servicios extra
    $extras = DB::table('reservacion_servicio as rs')
        ->leftJoin('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
        ->select('s.nombre', 'rs.precio_unitario')
        ->where('rs.id_reservacion', $reservacion->id_reservacion)
        ->get();

    // ============================
    // 6️⃣ Cálculos
    // ============================
    $subtotal =
        ($tarifaBase * $dias)
        + $paquetes->sum(fn($p) => $p->precio_por_dia * $dias)
        + $individuales->sum(fn($i) => $i->precio_por_dia * $dias)
        + $extras->sum(fn($e) => $e->precio_unitario * $dias);

    $iva     = $subtotal * 0.16;

    $totalFinal = $subtotal + $iva;

    // ============================
    // 7️⃣ Obtener vehículo asignado
    // ============================
    $vehiculo = null;

    if (!empty($reservacion->id_vehiculo)) {
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
    }

    // ============================
    // 8️⃣ Enviar datos a la vista
    // ============================
    return view('admin.ContratoFinal', [
        'contrato'     => $contrato,
        'reservacion'  => $reservacion,
        'licencia'     => $licencia,
        'vehiculo'     => $vehiculo,
        'dias'         => $dias,
        'tarifaBase'   => $tarifaBase,
        'paquetes'     => $paquetes,
        'individuales' => $individuales,
        'extras'       => $extras,
        'subtotal'     => $subtotal,
        'totalFinal'   => $totalFinal,
    ]);
}

public function guardarFirmaCliente(Request $request)
{
    DB::table('contratos')
        ->where('id_contrato', $request->id_contrato)
        ->update([
            'firma_cliente' => $request->firma
        ]);

    return response()->json(['ok' => true]);
}

public function guardarFirmaArr(Request $request)
{
    DB::table('contratos')
        ->where('id_contrato', $request->id_contrato)
        ->update([
            'firma_arrendador' => $request->firma
        ]);

    return response()->json(['ok' => true]);
}

public function exportarWord($id)
{
    // 1. Obtener contrato
    $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
    if (!$contrato) {
        abort(404, "Contrato no encontrado");
    }

    // 2. Cargar plantilla .docx (crea este archivo en /public/plantillas/)
    $template = new TemplateProcessor(public_path('plantillas/contrato.docx'));

    // 3. Reemplazar variables
    $template->setValue('nombre_cliente', $contrato->nombre_cliente);
    $template->setValue('fecha_inicio', $contrato->fecha_inicio);
    $template->setValue('fecha_fin', $contrato->fecha_fin);
    $template->setValue('total', number_format($contrato->total, 2));

    // 4. Guardar archivo temporal
    $outputFile = "Contrato_{$contrato->id_contrato}.docx";
    $template->saveAs(storage_path("app/public/{$outputFile}"));

    // 5. Descargar archivo
    return response()->download(storage_path("app/public/{$outputFile}"))
                     ->deleteFileAfterSend(true);
}

public function enviarContratoCorreo($id)
{
    // ============================
    // 1️⃣ Obtener contrato
    // ============================
    $contrato = DB::table('contratos')
        ->where('id_contrato', $id)
        ->first();

    if (!$contrato) {
        return response()->json(['ok' => false, 'msg' => 'Contrato no encontrado.']);
    }

    // ============================
    // 2️⃣ Obtener reservación
    // ============================
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

    if (!$reservacion || !$reservacion->email_cliente) {
        return response()->json([
            'ok'  => false,
            'msg' => 'La reservación no tiene correo registrado.'
        ]);
    }

    $correoDestino = $reservacion->email_cliente;

    // ============================
    // 3️⃣ Obtener licencia (opcional)
    // ============================
    $licencia = DB::table('contrato_documento')
        ->where('id_contrato', $id)
        ->where('tipo', 'licencia')
        ->whereNull('id_conductor')
        ->first()
        ?? DB::table('contrato_documento')
            ->where('id_contrato', $id)
            ->where('tipo', 'licencia')
            ->first();

    // ============================
    // 4️⃣ Calcular días
    // ============================
    $dias = \Carbon\Carbon::parse($reservacion->fecha_inicio)
        ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin));

    if ($dias == 0) $dias = 1;
    $dias++;

    // ============================
    // 5️⃣ Tarifas y protecciones
    // ============================
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

        $aviso = request()->input('aviso', '');



    $subtotal =
        ($tarifaBase * $dias)
        + $paquetes->sum(fn($p) => $p->precio_por_dia * $dias)
        + $individuales->sum(fn($i) => $i->precio_por_dia * $dias)
        + $extras->sum(fn($e) => $e->precio_unitario * $dias);

    $totalFinal = $subtotal + ($subtotal * 0.16);

    // ============================
    // 6️⃣ Vehículo
    // ============================
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

    // ============================
    // 7️⃣ Generar PDF
    // ============================
    try {
        $pdf = Pdf::loadView('Admin.contrato-final-pdf', [
            'contrato'     => $contrato,
            'reservacion'  => $reservacion,
            'licencia'     => $licencia,
            'vehiculo'     => $vehiculo,
            'dias'         => $dias,
            'tarifaBase'   => $tarifaBase,
            'paquetes'     => $paquetes,
            'individuales' => $individuales,
            'extras'       => $extras,
            'subtotal'     => $subtotal,
            'totalFinal'   => $totalFinal,
        ]);

        // 👇⬇️ AQUÍ EL TAMAÑO PERSONALIZADO — UNA SOLA HOJA GRANDE
$pdf->setPaper([0, 0, 1000, 1500], 'portrait');
    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'msg' => "Error al generar PDF: " . $e->getMessage()
        ]);
    }

    // ============================
    // 8️⃣ Guardar PDF temporal
    // ============================
    $filePath = storage_path("app/public/Contrato_{$id}.pdf");
    $pdf->save($filePath);

    // ============================
    // 9️⃣ Enviar correo
    // ============================
    try {
       Mail::to($correoDestino)->send(
    new ContratoFinalMail(
        $contrato,
        $reservacion,
        $licencia,
        $vehiculo,
        $dias,
        $totalFinal,
        $filePath,
        $aviso
    )
);


    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'msg' => "Error al enviar correo: " . $e->getMessage()
        ]);
    }

    return response()->json([
        'ok' => true,
        'msg' => "Contrato enviado a: {$correoDestino}"
    ]);
}





}
