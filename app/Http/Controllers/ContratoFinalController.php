<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Browsershot\Browsershot;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Mail\ContratoFinalMail;
use App\Models\ContratoRevision;

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

        // DOB fallback
        if (empty($reservacion->fecha_nacimiento)) {
            $docIdentTitular = DB::table('contrato_documento')
                ->where('id_contrato', $idContrato)
                ->where('tipo', 'identificacion')
                ->whereNotNull('fecha_nacimiento')
                ->orderBy('id_documento', 'asc')
                ->first();

            if ($docIdentTitular) {
                $reservacion->fecha_nacimiento = $docIdentTitular->fecha_nacimiento;
            }
        }

        // 3️⃣ Licencia
        $licencia = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->whereNull('id_conductor')
            ->where('tipo', 'licencia')
            ->first();

        $identificacion = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->where('tipo', 'identificacion')
            ->whereNotNull('fecha_nacimiento')
            ->orderBy('id_documento', 'asc')
            ->first();

        // Fecha de nacimiento: reservación primero, luego identificación
        $fechaNacimiento = $reservacion->fecha_nacimiento
            ?? ($identificacion->fecha_nacimiento ?? null);

        // Edad calculada desde la fecha de nacimiento
        $edad = !empty($fechaNacimiento)
            ? \Carbon\Carbon::parse($fechaNacimiento)->age
            : null;

        // 4️⃣ Días
        $dias = max(
            \Carbon\Carbon::parse($reservacion->fecha_inicio)
                ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin)),
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

        // OBTENER EXTRAS CON CANTIDAD (servicios normales de la tabla reservacion_servicio)
        $extras = DB::table('reservacion_servicio as rs')
            ->leftJoin('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
            ->select('s.nombre', 'rs.precio_unitario', 'rs.cantidad')
            ->where('rs.id_reservacion', $reservacion->id_reservacion)
            ->get();

        // 1. DELIVERY - Desde la tabla reservaciones (columnas delivery_*)
        $deliveryInfo = null;
        if ($reservacion->delivery_activo && ($reservacion->delivery_total ?? 0) > 0) {
            $deliveryInfo = (object) [
                'nombre' => 'Delivery',
                'precio_unitario' => $reservacion->delivery_total ?? 0,
                'cantidad' => 1,
                'activo' => $reservacion->delivery_activo,
                'direccion' => $reservacion->delivery_direccion ?? '',
                'kms' => $reservacion->delivery_km ?? 0,
            ];
        }

        // 2. DROPOFF - Desde la tabla cargo_adicional (id_concepto = 6)
        $dropoffInfo = null;
        $dropoffCargo = DB::table('cargo_adicional')
            ->where('id_contrato', $idContrato)
            ->where('id_concepto', 6)
            ->first();

        if ($dropoffCargo && ($dropoffCargo->monto ?? 0) > 0) {
            $dropoffInfo = (object) [
                'nombre' => 'Drop Off',
                'precio_unitario' => $dropoffCargo->monto ?? 0,
                'cantidad' => 1,
                'destino' => $dropoffCargo->destino ?? '',
                'km' => $dropoffCargo->km ?? 0,
            ];
        }

        // 3. GASOLINA - Desde la tabla cargo_adicional (id_concepto = 5)
        $gasolinaInfo = null;
        $gasolinaCargo = DB::table('cargo_adicional')
            ->where('id_contrato', $idContrato)
            ->where('id_concepto', 5)
            ->first();

        if ($gasolinaCargo && ($gasolinaCargo->monto ?? 0) > 0) {
            $gasolinaInfo = (object) [
                'nombre' => 'Gasolina (faltante)',
                'precio_unitario' => $gasolinaCargo->monto ?? 0,
                'cantidad' => $gasolinaCargo->litros ?? 1,
                'litros' => $gasolinaCargo->litros ?? 0,
            ];
        }

        $todosLosServicios = DB::table('servicios')
            ->select('id_servicio', 'nombre', 'precio', 'tipo_cobro')
            ->where('activo', true)
            ->get();

        // 6️⃣ Totales
        $subtotal =
            ($tarifaBase * $dias) +
            $paquetes->sum(fn($p) => $p->precio_por_dia * $dias) +
            $individuales->sum(fn($i) => $i->precio_por_dia * $dias) +
            $extras->sum(fn($e) => ($e->precio_unitario ?? 0) * ($e->cantidad ?? 1) * $dias);

        if ($deliveryInfo && ($deliveryInfo->precio_unitario ?? 0) > 0) {
            $subtotal += $deliveryInfo->precio_unitario;
        }
        if ($dropoffInfo && ($dropoffInfo->precio_unitario ?? 0) > 0) {
            $subtotal += $dropoffInfo->precio_unitario;
        }
        if ($gasolinaInfo && ($gasolinaInfo->precio_unitario ?? 0) > 0) {
            $subtotal += $gasolinaInfo->precio_unitario;
        }

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
                'v.firma_propietario',
                'v.capacidad_tanque',
                'v.placa',
                DB::raw('COALESCE(c.nombre, v.categoria) as categoria')
            )
            ->where('v.id_vehiculo', $reservacion->id_vehiculo)
            ->first();

            // 8️⃣ Detectar conductores adicionales reales
            $nombreTitular = trim(mb_strtoupper(
                ($reservacion->nombre_cliente ?? '') . ' ' .
                ($reservacion->apellidos_cliente ?? ''),
                'UTF-8'
            ));

            $conductoresContrato = DB::table('contrato_conductor_adicional')
                ->where('id_contrato', $idContrato)
                ->select(
                    'id_conductor',
                    'nombres',
                    'apellidos'
                )
                ->get();

            $conductoresAdicionales = $conductoresContrato
                ->filter(function ($conductor) use ($nombreTitular) {
                    $nombreConductor = trim(mb_strtoupper(
                        ($conductor->nombres ?? '') . ' ' .
                        ($conductor->apellidos ?? ''),
                        'UTF-8'
                    ));

                    return $nombreConductor !== $nombreTitular;
                })
                ->values();

            $tieneConductorAdicional = $conductoresAdicionales->isNotEmpty();

            // 9️⃣ Revisiones guardadas de este contrato
            $revisionesContrato = ContratoRevision::where(
                    'id_contrato',
                    $idContrato
                )
                ->where('revisado', true)
                ->pluck('revisado', 'seccion')
                ->map(fn ($revisado) => (bool) $revisado)
                ->toArray();

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
            'totalFinal',
            'deliveryInfo',
            'dropoffInfo',
            'gasolinaInfo',
            'todosLosServicios',
            'fechaNacimiento',
            'edad',
            'conductoresAdicionales',
            'tieneConductorAdicional',
            'revisionesContrato'
        ));
    }

    /* =========================================================
       REVISIONES DE LOS DOCUMENTOS DEL CONTRATO
    ========================================================= */

    public function guardarRevision(Request $request, $id)
    {
        $contrato = DB::table('contratos')
            ->where('id_contrato', $id)
            ->first();

        if (!$contrato) {
            return response()->json([
                'ok' => false,
                'msg' => 'Contrato no encontrado.',
            ], 404);
        }

        $datos = $request->validate([
            'seccion' => [
                'required',
                'string',
                'in:contrato,checklist,conductor_adicional,clausulas',
            ],
        ]);

        $revision = ContratoRevision::updateOrCreate(
            [
                'id_contrato' => $id,
                'seccion' => $datos['seccion'],
            ],
            [
                'revisado' => true,
                'revisado_por' => auth()->id(),
                'revisado_en' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
            'msg' => 'Apartado marcado como revisado.',
            'revision' => [
                'seccion' => $revision->seccion,
                'revisado' => $revision->revisado,
                'revisado_en' => optional($revision->revisado_en)->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function obtenerRevisiones($id)
    {
        $contratoExiste = DB::table('contratos')
            ->where('id_contrato', $id)
            ->exists();

        if (!$contratoExiste) {
            return response()->json([
                'ok' => false,
                'msg' => 'Contrato no encontrado.',
            ], 404);
        }

        $revisiones = ContratoRevision::where('id_contrato', $id)
            ->where('revisado', true)
            ->get()
            ->mapWithKeys(function ($revision) {
                return [
                    $revision->seccion => [
                        'revisado' => true,
                        'revisado_en' => optional($revision->revisado_en)
                            ->format('d/m/Y H:i'),
                    ],
                ];
            });

        return response()->json([
            'ok' => true,
            'revisiones' => $revisiones,
        ]);
    }

    /* =========================================================
       GUARDAR FIRMAS
    ========================================================= */

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
        // 1️⃣ CONTRATO
        $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
        if (!$contrato) {
            return response()->json(['ok' => false, 'msg' => 'Contrato no encontrado']);
        }

        // 2️⃣ RESERVACIÓN (MISMO JOIN QUE mostrarContratoFinal)
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

        if (!$reservacion || empty($reservacion->email_cliente)) {
            return response()->json(['ok' => false, 'msg' => 'Correo del cliente no disponible']);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR QUE TODOS LOS DOCUMENTOS ESTÉN REVISADOS
        |--------------------------------------------------------------------------
        */

        $seccionesObligatorias = [
            'contrato',
            'clausulas',
            'checklist',
        ];

        // Detectar si realmente existe un conductor adicional
        $nombreTitular = trim(mb_strtoupper(
            ($reservacion->nombre_cliente ?? '') . ' ' .
            ($reservacion->apellidos_cliente ?? ''),
            'UTF-8'
        ));

        $conductoresContrato = DB::table('contrato_conductor_adicional')
            ->where('id_contrato', $id)
            ->select(
                'nombres',
                'apellidos'
            )
            ->get();

        $tieneConductorAdicional = $conductoresContrato
            ->contains(function ($conductor) use ($nombreTitular) {
                $nombreConductor = trim(mb_strtoupper(
                    ($conductor->nombres ?? '') . ' ' .
                    ($conductor->apellidos ?? ''),
                    'UTF-8'
                ));

                return $nombreConductor !== $nombreTitular;
            });

        if ($tieneConductorAdicional) {
            $seccionesObligatorias[] = 'conductor_adicional';
        }

        $seccionesRevisadas = ContratoRevision::where(
                'id_contrato',
                $id
            )
            ->where('revisado', true)
            ->whereIn('seccion', $seccionesObligatorias)
            ->pluck('seccion')
            ->toArray();

        $seccionesFaltantes = array_values(
            array_diff(
                $seccionesObligatorias,
                $seccionesRevisadas
            )
        );

        if (!empty($seccionesFaltantes)) {
            $nombresSecciones = [
                'contrato' => 'Contrato',
                'clausulas' => 'Cláusulas',
                'checklist' => 'Checklist',
                'conductor_adicional' => 'Conductor adicional',
            ];

            $faltantesTexto = collect($seccionesFaltantes)
                ->map(fn ($seccion) => $nombresSecciones[$seccion] ?? $seccion)
                ->implode(', ');

            return response()->json([
                'ok' => false,
                'msg' => 'No se puede enviar el correo. Falta revisar: ' .
                    $faltantesTexto,
            ], 422);
        }

        if (empty($reservacion->fecha_nacimiento)) {

            $docIdentTitular = DB::table('contrato_documento')
                ->where('id_contrato', $id)
                ->where('tipo', 'identificacion')
                ->whereNotNull('fecha_nacimiento')
                ->orderBy('id_documento', 'asc')
                ->first();

            if ($docIdentTitular) {
                $reservacion->fecha_nacimiento = $docIdentTitular->fecha_nacimiento;
            }
        }

        // 3️⃣ LICENCIA DEL TITULAR
        $licencia = DB::table('contrato_documento')
            ->where('id_contrato', $id)
            ->whereNull('id_conductor')
            ->where('tipo', 'licencia')
            ->first();

        // IDENTIFICACIÓN DEL TITULAR
        $identificacion = DB::table('contrato_documento')
            ->where('id_contrato', $id)
            ->whereNull('id_conductor')
            ->where('tipo', 'identificacion')
            ->whereNotNull('fecha_nacimiento')
            ->orderBy('id_documento', 'asc')
            ->first();

        // Fecha de nacimiento
        $fechaNacimiento = $reservacion->fecha_nacimiento
            ?? ($identificacion->fecha_nacimiento ?? null);

        // Edad
        $edad = !empty($fechaNacimiento)
            ? \Carbon\Carbon::parse($fechaNacimiento)->age
            : null;

        // 4️⃣ DÍAS  (MISMA FÓRMULA QUE mostrarContratoFinal)
        $dias = max(
            \Carbon\Carbon::parse($reservacion->fecha_inicio)
                ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin)),
            1
        );

        // 5️⃣ TARIFA BASE
        $tarifaBase = $reservacion->tarifa_modificada ?? $reservacion->tarifa_base ?? 0;

        // 6️⃣ PAQUETES, INDIVIDUALES, EXTRAS (MISMO CÓDIGO)
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

        // 7️⃣ SUBTOTAL Y TOTAL (IGUAL QUE EN LA VISTA)
        $subtotal =
            ($tarifaBase * $dias) +
            $paquetes->sum(fn($p) => $p->precio_por_dia * $dias) +
            $individuales->sum(fn($i) => $i->precio_por_dia * $dias) +
            $extras->sum(fn($e) => $e->precio_unitario * $dias);

        $totalFinal = $subtotal * 1.16;

        // 8️⃣ VEHÍCULO (MISMO JOIN)
        $vehiculo = DB::table('vehiculos as v')
            ->leftJoin('categorias_carros as c', 'v.id_categoria', '=', 'c.id_categoria')
            ->select(
                'v.modelo',
                'v.color',
                'v.transmision',
                'v.kilometraje',
                'v.gasolina_actual',
                'v.placa',
                'v.firma_propietario',
                DB::raw('COALESCE(c.nombre, v.categoria) as categoria')
            )
            ->where('v.id_vehiculo', $reservacion->id_vehiculo)
            ->first();

        // 9️⃣ TEXTO DEL AVISO
        $aviso = $request->input('aviso', '');

        // 🔟 FIRMA DEL AVISO (base64 desde el modal)
        $firmaAviso = $request->input('firma_aviso', null);

        // Guardar la firma del aviso en la tabla contratos (si viene)
        if ($firmaAviso) {
            DB::table('contratos')
                ->where('id_contrato', $id)
                ->update(['firma_aviso' => $firmaAviso]);

            $contrato->firma_aviso = $firmaAviso;
        }
        $contrato = DB::table('contratos')->where('id_contrato', $id)->first();
        // ======================================================
        // DATOS HOJA 2: lugar/fecha + arrendador/arrendatario
        // ======================================================
        \Carbon\Carbon::setLocale('es');

        // Lugar (sucursal retiro)
        $lugarFirma = $reservacion->sucursal_retiro_nombre
            ?? '—';

        // Fecha inicio reserva -> día/mes/año
        $fechaInicio = !empty($reservacion->fecha_inicio)
            ? \Carbon\Carbon::parse($reservacion->fecha_inicio)
            : null;

        $diaFirma  = $fechaInicio ? $fechaInicio->format('d') : '—';
        $mesFirma  = $fechaInicio ? ucfirst($fechaInicio->translatedFormat('F')) : '—';
        $anioFirma = $fechaInicio ? $fechaInicio->format('Y') : '—';

        // Quién hizo la reservación (arrendador): contrato.id_asesor > reservacion.id_asesor > reservacion.id_usuario
        $idArrendador = $contrato->id_asesor
            ?? $reservacion->id_asesor
            ?? $reservacion->id_usuario
            ?? null;

        // Nombre del arrendador desde usuarios
        $arrendadorNombre = '—';
        if (!empty($idArrendador)) {
            $arr = DB::table('usuarios')
                ->select('nombres', 'apellidos')
                ->where('id_usuario', $idArrendador)
                ->first();

            if ($arr) {
                $arrendadorNombre = trim(($arr->nombres ?? '') . ' ' . ($arr->apellidos ?? ''));
                if ($arrendadorNombre === '') $arrendadorNombre = '—';
            }
        }

        // Nombre del arrendatario (cliente) (sale de reservación)
        $arrendatarioNombre = trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? ''));
        if ($arrendatarioNombre === '') {
            $arrendatarioNombre = $reservacion->nombre_cliente ?? '—';
        }


        // 1️⃣1️⃣ GENERAR PDF (CHROMIUM) CON TODOS LOS DATOS
        $html = view('Admin.contrato-final-pdf', [
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
            'fechaNacimiento' => $fechaNacimiento,
            'edad'             => $edad,
            // ✅ HOJA 2: fecha/lugar
            'lugarFirma'   => $lugarFirma,
            'diaFirma'     => $diaFirma,
            'mesFirma'     => $mesFirma,
            'anioFirma'    => $anioFirma,

            // ✅ HOJA 2: nombres firmas
            'arrendadorNombre'   => $arrendadorNombre,
            'arrendatarioNombre' => $arrendatarioNombre,
        ])->render();

        $filePath = storage_path("app/public/Contrato_{$contrato->numero_contrato}.pdf");

        Browsershot::html($html)
            ->format('A4')
            ->margins(6, 6, 6, 6)
            ->showBackground()
            ->save($filePath);

        // 1️⃣2️⃣ ENVIAR CORREO
        $correoReservaciones = config('mail.from.address');

        Mail::to($reservacion->email_cliente)
            ->bcc($correoReservaciones)
            ->send(
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


        return response()->json([
            'ok'  => true,
            'msg' => 'Contrato enviado correctamente'
        ]);
    }
}
