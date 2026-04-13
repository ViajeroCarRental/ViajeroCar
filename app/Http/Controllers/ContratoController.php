<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class ContratoController extends ContratoBaseController
{
    /**
     * 📄 Mostrar un contrato específico a partir del ID de reservación.
     */
    public function mostrarContrato($id)
    {
        try {
            $asesorId = session('id_usuario');

            $contrato = DB::table('contratos')->where('id_contrato', $id)->first();

            if (!$contrato) {
                $contrato = DB::table('contratos')
                    ->where('id_reservacion', $id)
                    ->orderBy('id_contrato', 'desc')
                    ->first();
            }

            $idReservacion = $contrato ? $contrato->id_reservacion : $id;

            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('sucursales as sr', 'r.sucursal_retiro', '=', 'sr.id_sucursal')
                ->leftJoin('sucursales as se', 'r.sucursal_entrega', '=', 'se.id_sucursal')
                ->leftJoin('categoria_costo_km as cck', 'r.id_categoria', '=', 'cck.id_categoria')
                ->select(
                    'r.*',
                    'sr.nombre as sucursal_retiro_nombre',
                    'se.nombre as sucursal_entrega_nombre',
                    'cck.costo_km'
                )
                ->where('r.id_reservacion', $idReservacion)
                ->first();

            if (!$reservacion) {
                return redirect()->back()->with('error', 'Reservacion no encontrada.');
            }

            $categorias  = cache()->remember('cat_carros', 86400, fn() => DB::table('categorias_carros')->orderBy('nombre')->get());
            $servicios   = cache()->remember('cat_servicios_contrato', 86400, fn() => DB::table('servicios')->where('activo', true)->whereIn('id_servicio', [1, 4, 5, 7, 8, 11])->get());
            $ubicaciones = cache()->remember('cat_ubicaciones', 86400, fn() => DB::table('ubicaciones_servicio')->where('activo', 1)->orderBy('estado')->get());
            $seguros     = cache()->remember('cat_seguros', 86400, fn() => DB::table('seguro_paquete')->where('activo', true)->select('id_paquete as id_seguro', 'nombre', 'descripcion as cobertura', 'precio_por_dia')->get());
            $individuales = cache()->remember('cat_individuales', 86400, fn() => DB::table('seguro_individuales')->where('activo', true)->get());

            $catActual = $categorias->firstWhere('id_categoria', $reservacion->id_categoria);

            if (!$contrato) {
                $timezone = 'America/Mexico_City';
                $ahora = \Carbon\Carbon::now($timezone);

                $fechaInicioOriginal  = \Carbon\Carbon::parse($reservacion->fecha_inicio, $timezone)->startOfDay();
                $fechaInicioNuevaCalc = $ahora->copy()->startOfDay();
                $fechaFin             = \Carbon\Carbon::parse($reservacion->fecha_fin, $timezone)->startOfDay();

                $diasOriginales = max(1, $fechaInicioOriginal->diffInDays($fechaFin));
                $diasNuevos     = max(1, $fechaInicioNuevaCalc->diffInDays($fechaFin));
                $diferenciaDias = $diasNuevos - $diasOriginales;

                $precioDia = $catActual->precio_dia ?? 0;

                $ajusteSubtotal = $diferenciaDias * $precioDia;
                $ajusteIVA      = $ajusteSubtotal * 0.16;

                $subtotal  = $reservacion->subtotal + $ajusteSubtotal;
                $impuestos = $reservacion->impuestos + $ajusteIVA;
                $total     = $subtotal + $impuestos;

                DB::table('reservaciones')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->update([
                        'fecha_inicio' => $ahora->toDateString(),
                        'hora_retiro'  => $ahora->toTimeString(),
                        'subtotal'     => $subtotal,
                        'impuestos'    => $impuestos,
                        'total'        => $total,
                        'updated_at'   => now(),
                    ]);

                $reservacion->fecha_inicio = $ahora->toDateString();
                $reservacion->hora_retiro  = $ahora->toTimeString();
                $reservacion->subtotal     = $subtotal;
                $reservacion->impuestos    = $impuestos;
                $reservacion->total        = $total;

                $contrato = DB::transaction(function () use ($reservacion, $asesorId) {
                    $nuevoId = DB::table('contratos')->insertGetId([
                        'id_reservacion'  => $reservacion->id_reservacion,
                        'id_asesor'       => $asesorId,
                        'numero_contrato' => 'TEMP',
                        'estado'          => 'abierto',
                        'abierto_en'      => now(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    $folioFormateado = str_pad($nuevoId, 4, '0', STR_PAD_LEFT);
                    DB::table('contratos')
                        ->where('id_contrato', $nuevoId)
                        ->update(['numero_contrato' => $folioFormateado]);
                    return (object) [
                        'id_contrato' => $nuevoId,
                        'id_reservacion' => $reservacion->id_reservacion,
                        'id_asesor' => $asesorId,
                        'numero_contrato' => $folioFormateado,
                        'estado' => 'abierto',
                        'abierto_en' => now(),
                    ];
                });
            } else {
                if ($asesorId && is_null($contrato->id_asesor)) {
                    DB::table('contratos')
                        ->where('id_contrato', $contrato->id_contrato)
                        ->update(['id_asesor' => $asesorId]);
                    $contrato->id_asesor = $asesorId;
                }
            }

            if ($asesorId && ($reservacion->id_asesor ?? null) != $asesorId) {
                DB::table('reservaciones')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->update(['id_asesor' => $asesorId]);
                $reservacion->id_asesor = $asesorId;
            }

            $nombresExcluidos = ['Gasolina Prepago', 'Drop Off', 'Dropoff', 'Delivery', 'Servicio de Delivery'];
            $servicios = $servicios->filter(function ($servicio) use ($nombresExcluidos) {
                return !in_array($servicio->nombre, $nombresExcluidos);
            })->values();

            $individuales_col   = collect($individuales);
            $grupo_colision     = $individuales_col->filter(fn($i) => str_contains($i->nombre, 'LDW') || str_contains($i->nombre, 'PDW') || str_contains($i->nombre, 'CDW'));
            $grupo_medicos      = $individuales_col->filter(fn($i) => str_contains($i->nombre, 'PAI'));
            $grupo_asistencia   = $individuales_col->filter(fn($i) => str_contains($i->nombre, 'PRA'));
            $grupo_terceros     = $individuales_col->filter(fn($i) => str_contains($i->nombre, 'LI'));
            $grupo_protecciones = $individuales_col->filter(fn($i) => str_contains($i->nombre, 'LOU') || str_contains($i->nombre, 'LA'));

            view()->share([
                'grupo_colision'     => $grupo_colision,
                'grupo_medicos'      => $grupo_medicos,
                'grupo_asistencia'   => $grupo_asistencia,
                'grupo_terceros'     => $grupo_terceros,
                'grupo_protecciones' => $grupo_protecciones,
            ]);

            // IMAGEN FIJA POR CATEGORÍA
            $codigoCat  = $catActual->codigo ?? 'C';

            $mapaImagenes = [
                'C'  => 'aveo.png',
                'D'  => 'virtus.png',
                'E'  => 'jetta.png',
                'F'  => 'camry.png',
                'IC' => 'renegade.png',
                'I'  => 'taos.png',
                'IB' => 'avanza.png',
                'M'  => 'Odyssey.png',
                'L'  => 'Hiace.png',
                'H'  => 'Frontier.png',
                'HI' => 'Tacoma.png'
            ];

            $archivo  = $mapaImagenes[$codigoCat] ?? 'Logotipo.png';
            $imgFinal = asset("img/$archivo");

            $vehiculo = $reservacion->id_vehiculo
                ? DB::table('vehiculos')->where('id_vehiculo', $reservacion->id_vehiculo)->first()
                : null;

            if ($vehiculo) {
                $vehiculo->imagen_render = $imgFinal;
            } else {
                $vehiculo = (object)[
                    'imagen_render'  => $imgFinal,
                    'puertas'        => 0,
                    'asientos'       => 0,
                    'transmision'    => 'N/A',
                    'nombre_publico' => $catActual->nombre ?? 'Vehículo'
                ];
            }

            $fechaInicio = Carbon::parse($reservacion->fecha_inicio ?? now());
            $fechaFin = Carbon::parse($reservacion->fecha_fin ?? now()->addDay());
            $horaRetiro = Carbon::parse($reservacion->hora_retiro ?? '12:00:00');
            $horaEntrega = Carbon::parse($reservacion->hora_entrega ?? '12:00:00');

            $diasTotales = max(1, $fechaInicio->diffInDays($fechaFin));

            $precioBase = $catActual->precio_dia ?? ($catActual->precio ?? 0);
            $esAjustada = $reservacion->tarifa_ajustada ?? 0;
            $tarifaModificada = $reservacion->tarifa_modificada ?? 0;
            $precioReal = $esAjustada == 1 && $tarifaModificada > 0 ? $tarifaModificada : $precioBase;

            $subtotal = $diasTotales * $precioReal;
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;

            $telOriginal = $reservacion->telefono_cliente ?? '';
            $soloNumeros = preg_replace('/[^0-9]/', '', $telOriginal);

            if (strlen($soloNumeros) == 12) {
                $telFinal =
                    '+52 (' .
                    substr($soloNumeros, 2, 3) .
                    ') ' .
                    substr($soloNumeros, 5, 3) .
                    '-' .
                    substr($soloNumeros, 8);
            } elseif (strlen($soloNumeros) == 10) {
                $telFinal =
                    '(' . substr($soloNumeros, 0, 3) . ') ' . substr($soloNumeros, 3, 3) . '-' . substr($soloNumeros, 6);
            } else {
                $telFinal = $telOriginal ?: '--';
            }

            $nivelNumerico = $vehiculo->gasolina_actual ?? 16;
            $nivelFraccion = $nivelNumerico . '/16';

            // Totales, Seguros y Servicios
            $seguroSeleccionado = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
                ->select('sp.id_paquete as id_seguro', 'sp.nombre', 'sp.precio_por_dia')
                ->where('rps.id_reservacion', $reservacion->id_reservacion)
                ->first();

            $serviciosReservados = DB::table('reservacion_servicio')
                ->where('id_reservacion', $reservacion->id_reservacion)
                ->pluck('cantidad', 'id_servicio')
                ->toArray();

            $delivery = (object)[
                'activo'       => $reservacion->delivery_activo,
                'id_ubicacion' => $reservacion->delivery_ubicacion,
                'direccion'    => $reservacion->delivery_direccion,
                'kms'          => $reservacion->delivery_km,
                'precio_km'    => $reservacion->delivery_precio_km,
                'total'        => $reservacion->delivery_total
            ];

            $cargosActivosIds    = [];
            $cargoGas            = null;
            $cargoDrop           = null;
            $totalPaso2Variables = 0;

            if ($contrato) {
                $cargosDelContrato = DB::table('cargo_adicional')
                    ->where('id_contrato', $contrato->id_contrato)
                    ->get();

                $cargosActivosIds    = $cargosDelContrato->pluck('id_concepto')->toArray();
                $cargoGas            = $cargosDelContrato->where('id_concepto', 5)->first();
                $cargoDrop           = $cargosDelContrato->where('id_concepto', 6)->first();
                $totalPaso2Variables = $cargosDelContrato->sum('monto');
            }

            $costoKmCategoria = $reservacion->costo_km ?? 0;

            return view('Admin.Contrato', [
                'reservacion'         => $reservacion,
                'vehiculo'            => $vehiculo,
                'seguros'             => $seguros,
                'servicios'           => $servicios,
                'seguroSeleccionado'  => $seguroSeleccionado,
                'contrato'            => $contrato,
                'categorias'          => $categorias,
                'ubicaciones'         => $ubicaciones,
                'costoKmCategoria'    => $costoKmCategoria,
                'delivery'            => $delivery,
                'individuales'        => $individuales,
                'serviciosReservados' => $serviciosReservados,
                'cargosActivos'       => $cargosActivosIds,
                'cargoGas'            => $cargoGas,
                'cargoDrop'           => $cargoDrop,
                'totalPaso4Server'    => $totalPaso2Variables,
                'catActual'           => $catActual,
                'fechaInicio'         => $fechaInicio,
                'fechaFin'            => $fechaFin,
                'horaRetiro'          => $horaRetiro,
                'horaEntrega'         => $horaEntrega,
                'diasTotales'         => $diasTotales,
                'precioBase'          => $precioBase,
                'precioReal'          => $precioReal,
                'subtotal'            => $subtotal,
                'iva'                 => $iva,
                'total'               => $total,
                'telFinal'            => $telFinal,
                'codigoCat'           => $codigoCat,
                'imgFinal'            => $imgFinal,
                'nivelNumerico'       => $nivelNumerico,
                'nivelFraccion'       => $nivelFraccion,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ContratoController@mostrarContrato: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrio un error al cargar la reservacion.');
        }
    }

    public function solicitarCambioFecha(Request $request)
    {
        try {

            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'nueva_fecha'    => 'required|date',
                'nueva_hora'     => 'nullable',
                'motivo'         => 'nullable|string|max:500'
            ]);

            // Reservación original
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->first();

            if (!$res) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

            // Crear token único
            $token = bin2hex(random_bytes(32));

            // Guardar solicitud
            DB::table('contrato_cambio_fecha')->insert([
                'id_reservacion'   => $res->id_reservacion,
                'fecha_anterior'   => $res->fecha_inicio,
                'hora_anterior'    => $res->hora_retiro,
                'fecha_solicitada' => $data['nueva_fecha'],
                'hora_solicitada'  => $data['nueva_hora'],
                'motivo'           => $data['motivo'] ?? null,
                'token'            => $token,
                'estado'           => 'pendiente',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $superadminEmail = env('ADMIN_NOTIFICATION_EMAIL');

            $linkAprobar  = url("/admin/contrato/cambio-fecha/aprobar/{$token}");
            $linkRechazar = url("/admin/contrato/cambio-fecha/rechazar/{$token}");

            $html = "
            <div style='font-family:sans-serif;'>
                <h2 style='color:#D6121F;'>Solicitud de cambio de fecha</h2>

                <p><b>Reservación:</b> {$res->codigo}</p>
                <p><b>Cliente:</b> {$res->nombre_cliente}</p>

                <p><b>Fecha actual:</b> {$res->fecha_inicio} {$res->hora_retiro}</p>
                <p><b>Nueva fecha solicitada:</b> {$data['nueva_fecha']} {$data['nueva_hora']}</p>

                <p><b>Motivo:</b> " . ($data['motivo'] ?? 'No especificado') . "</p>

                <p>Acciones:</p>

                <p>
                    <a href='{$linkAprobar}'
                       style='background:#16a34a;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;'>
                        Aprobar cambio
                    </a>
                </p>

                <p>
                    <a href='{$linkRechazar}'
                       style='background:#dc2626;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;'>
                        Rechazar solicitud
                    </a>
                </p>
            </div>
        ";

            try {
                Mail::html($html, function ($message) use ($superadminEmail) {
                    $message->to($superadminEmail)
                        ->from(config('mail.from.address'), 'Sistema Viajero Car Rental')
                        ->subject("Solicitud de cambio de fecha");
                });

                return response()->json(['success' => true, 'msg' => 'Solicitud enviada.']);
            } catch (\Exception $e) {
                Log::error("Error de correo: " . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return response()->json([
                'success' => true,
                'msg'     => 'Solicitud enviada al superadministrador.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en solicitarCambioFecha: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno.'], 500);
        }
    }

    public function aprobarCambioFecha($token)
    {
        try {
            $sol = DB::table('contrato_cambio_fecha')
                ->where('token', $token)
                ->where('estado', 'pendiente')
                ->first();

            if (!$sol) {
                return "Solicitud inválida o ya procesada.";
            }

            // Actualizar la reservación
            DB::table('reservaciones')
                ->where('id_reservacion', $sol->id_reservacion)
                ->update([
                    'fecha_inicio' => $sol->fecha_solicitada,
                    'hora_retiro'  => $sol->hora_solicitada,
                    'aprobado_por_superadmin' => true,
                    'updated_at' => now(),
                ]);

            // Recalcular totales con nueva fecha
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $sol->id_reservacion)
                ->first();

            $this->recalcularYActualizarTotales(
                new Request([
                    'fecha_inicio' => $sol->fecha_solicitada,
                    'hora_inicio'  => $sol->hora_solicitada,
                    'fecha_fin'    => $res->fecha_fin,
                    'hora_fin'     => $res->hora_entrega,
                    'id_categoria' => $res->id_categoria,
                ]),
                $sol->id_reservacion
            );



            // Marcar solicitud como aprobada
            DB::table('contrato_cambio_fecha')
                ->where('id', $sol->id)
                ->update([
                    'estado' => 'aprobado',
                    'autorizado_por' => 'superadmin',
                    'fecha_autorizacion' => now()
                ]);

            return "
            <h2 style='font-family:sans-serif;color:#16a34a'>Cambio aprobado ✔</h2>
            <p>La fecha ha sido actualizada exitosamente.</p>
        ";
        } catch (\Throwable $e) {
            Log::error("Error en aprobarCambioFecha: " . $e->getMessage());
            return "Error interno.";
        }
    }

    public function rechazarCambioFecha($token)
    {
        try {
            $sol = DB::table('contrato_cambio_fecha')
                ->where('token', $token)
                ->where('estado', 'pendiente')
                ->first();

            if (!$sol) {
                return "Solicitud inválida o ya procesada.";
            }

            DB::table('contrato_cambio_fecha')
                ->where('id', $sol->id)
                ->update([
                    'estado' => 'rechazado',
                    'autorizado_por' => 'superadmin',
                    'fecha_autorizacion' => now()
                ]);

            return "
            <h2 style='font-family:sans-serif;color:#dc2626'>Cambio rechazado ❌</h2>
            <p>No se realizaron modificaciones en la reservación.</p>
        ";
        } catch (\Throwable $e) {
            Log::error("Error en rechazarCambioFecha: " . $e->getMessage());
            return "Error interno.";
        }
    }

    public function estadoCambioFecha($idReservacion)
    {
        $registro = DB::table('contrato_cambio_fecha')
            ->where('id_reservacion', $idReservacion)
            ->orderBy('id', 'desc')
            ->first();

        if (!$registro) {
            return response()->json(["estado" => "sin-solicitud"]);
        }

        return response()->json([
            "estado" => $registro->estado,
            "fecha_nueva" => $registro->fecha_solicitada
        ]);
    }

    public function recalcularYActualizarTotales(Request $request, $idReservacion)
    {
        try {
            $data = $request->validate([
                'fecha_inicio'   => 'required|date',
                'hora_inicio'    => 'nullable',
                'fecha_fin'      => 'required|date',
                'hora_fin'       => 'nullable',
                'id_categoria'   => 'required|integer|exists:categorias_carros,id_categoria',
                'tarifa_manual'  => 'nullable|numeric', // Permite recibir el nuevo precio
                'horas_cortesia' => 'nullable|integer', // Permite recibir las nuevas horas
            ]);

            $res = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();

            if (!$res) return response()->json(['error' => 'No encontrada'], 404);

            $categoria = DB::table('categorias_carros')->where('id_categoria', $data['id_categoria'])->first();

            $cambioDeCategoria = ($res->id_categoria != $data['id_categoria']);
            $tarifaModificada = $res->tarifa_modificada;

            if (!empty($data['tarifa_manual']) && $data['tarifa_manual'] > 0) {
                $precioReal = $data['tarifa_manual'];
                $nuevaTarifaAjustada = 1;
                $tarifaModificada = $precioReal; // Guardamos el nuevo ajuste
            } elseif ($cambioDeCategoria) {
                $precioReal = $categoria->precio_dia;
                $nuevaTarifaAjustada = 0;
                $tarifaModificada = 0;
            } else {
                if ($res->tarifa_ajustada == 1 && $res->tarifa_modificada > 0) {
                    $precioReal = $res->tarifa_modificada;
                    $nuevaTarifaAjustada = 1;
                } else {
                    $precioReal = $categoria->precio_dia;
                    $nuevaTarifaAjustada = 0;
                    $tarifaModificada = 0;
                }
            }

            $fechaI = \Carbon\Carbon::parse($data['fecha_inicio']);
            $fechaF = \Carbon\Carbon::parse($data['fecha_fin']);
            $dias = max(1, $fechaI->diffInDays($fechaF));

            $subtotal = $dias * $precioReal;
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;

            $horasCortesiaFinal = isset($data['horas_cortesia']) ? $data['horas_cortesia'] : $res->horas_cortesia;

            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'id_categoria'      => $data['id_categoria'],
                    'tarifa_base'       => $precioReal,
                    'tarifa_modificada' => $tarifaModificada,
                    'tarifa_ajustada'   => $nuevaTarifaAjustada,
                    'horas_cortesia'    => $horasCortesiaFinal,
                    'fecha_inicio'      => $data['fecha_inicio'],
                    'hora_retiro'       => $data['hora_inicio'],
                    'fecha_fin'         => $data['fecha_fin'],
                    'hora_entrega'      => $data['hora_fin'],
                    'subtotal'          => $subtotal,
                    'impuestos'         => $iva,
                    'total'             => $total,
                    'updated_at'        => now(),
                ]);

            return response()->json([
                'success'          => true,
                'dias'             => $dias,
                'precio_dia'       => $precioReal, // Mandamos raw para que window.money lo procese
                'horas_cortesia'   => $horasCortesiaFinal, // Le avisamos al JS que sí se guardó
                'total'            => $total,
                'total_formateado' => number_format($total, 2),
                'subtotal'         => $subtotal,
                'impuestos'        => $iva,
                'fecha_inicio'     => $data['fecha_inicio'],
                'fecha_fin'        => $data['fecha_fin'],
                'hora_inicio'      => \Carbon\Carbon::parse($data['hora_inicio'])->format('h:i A'),
                'hora_fin'         => \Carbon\Carbon::parse($data['hora_fin'])->format('h:i A'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function actualizarCategoria(Request $request, $idReservacion)
    // {
    //     try {
    //         $data = $request->validate([
    //             'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria'
    //         ]);

    //         // 1️⃣ Cargar reservación actual
    //         $res = DB::table('reservaciones')
    //             ->where('id_reservacion', $idReservacion)
    //             ->first();

    //         if (!$res) {
    //             return response()->json([
    //                 'success' => false,
    //                 'error'   => 'Reservación no encontrada.'
    //             ], 404);
    //         }

    //         // 2️⃣ Cargar categoría nueva para sacar tarifa base real
    //         $categoria = DB::table('categorias_carros')
    //             ->where('id_categoria', $data['id_categoria'])
    //             ->first();

    //         if (!$categoria) {
    //             return response()->json([
    //                 'success' => false,
    //                 'error'   => 'Categoría no encontrada.'
    //             ], 404);
    //         }

    //         // 3️⃣ Flags para el frontend
    //         $vehiculoRemovido = !is_null($res->id_vehiculo);
    //         $tarifaLimpiada   = ($res->tarifa_ajustada == 1) || (!is_null($res->tarifa_modificada) && $res->tarifa_modificada > 0);

    //         // 4️⃣ Actualizar reservación según tu flujo C
    //         DB::table('reservaciones')
    //             ->where('id_reservacion', $idReservacion)
    //             ->update([
    //                 // Categoría nueva
    //                 'id_categoria'     => $data['id_categoria'],

    //                 // Siempre quitar vehículo al cambiar categoría (opción C)
    //                 'id_vehiculo'      => null,

    //                 // Reset total de tarifa modificada
    //                 'tarifa_ajustada'  => 0,
    //                 'tarifa_modificada' => null,

    //                 // Fijar nueva tarifa base del catálogo
    //                 'tarifa_base'      => $categoria->precio_dia,

    //                 'updated_at'       => now(),
    //             ]);

    //         // return response()->json([
    //         //     'success'          => true,
    //         //     'msg'              => 'Categoría actualizada correctamente.',
    //         //     'vehiculo_removido' => $vehiculoRemovido,
    //         //     'tarifa_limpiada'  => $tarifaLimpiada,
    //         //     'tarifa_base_nueva' => number_format($categoria->precio_dia, 2),
    //         // ]);
    //         return $this->resumenContrato($idReservacion);
    //     } catch (\Throwable $e) {
    //         Log::error("Error en actualizarCategoria: " . $e->getMessage(), [
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'error'   => 'Error interno al guardar la categoría.'
    //         ], 500);
    //     }
    // }

    public function categoriaInfo($codigo)
    {
        try {
            $cat = DB::table('categorias_carros')
                ->where('codigo', $codigo)
                ->first();

            if (!$cat) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Categoría no encontrada'
                ], 404);
            }

            return response()->json([
                'success'   => true,
                'categoria' => $cat
            ]);
        } catch (\Throwable $e) {
            Log::error("Error categoriaInfo: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Error interno'
            ], 500);
        }
    }

    protected function recalcularResumenServiciosReservacion(int $idReservacion): array
    {
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) {
            throw new \RuntimeException('Reservación no encontrada para recalcular servicios.');
        }

        $dias = $this->calcularDiasRenta($res->fecha_inicio, $res->fecha_fin);
        $precioRenta = ($res->tarifa_modificada > 0) ? $res->tarifa_modificada : $res->tarifa_base;
        $subtotalRenta = $precioRenta * $dias;

        $serviciosAgregados = DB::table('reservacion_servicio as rs')
            ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
            ->where('rs.id_reservacion', $idReservacion)
            ->select('s.nombre', 's.tipo_cobro', 'rs.cantidad', 'rs.precio_unitario')
            ->get();

        $listaServicios = [];
        $totalServicios = 0;

        if ($res->delivery_activo && (float)($res->delivery_total ?? 0) > 0) {
            $deliveryTotal = (float)$res->delivery_total;
            $listaServicios[] = ['nombre' => 'Delivery', 'cantidad' => 1, 'total' => $deliveryTotal];
            $totalServicios += $deliveryTotal;
        }

        foreach ($serviciosAgregados as $srv) {
            $totalLinea = $srv->tipo_cobro === 'por_dia'
                ? ((float)$srv->precio_unitario * (int)$srv->cantidad * $dias)
                : ((float)$srv->precio_unitario * (int)$srv->cantidad);

            $listaServicios[] = [
                'nombre' => $srv->nombre,
                'cantidad' => (int)$srv->cantidad,
                'total' => $totalLinea,
            ];
            $totalServicios += $totalLinea;
        }

        $nuevoSubtotal = $subtotalRenta + $totalServicios;
        $nuevoIva = $nuevoSubtotal * 0.16;
        $nuevoTotal = $nuevoSubtotal + $nuevoIva;

        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'subtotal'   => $nuevoSubtotal,
                'impuestos'  => $nuevoIva,
                'total'      => $nuevoTotal,
                'updated_at' => now(),
            ]);

        $pagosRealizados = DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->where('estatus', 'paid')
            ->where(function ($query) {
                $query->whereNull('tipo_pago')
                    ->orWhereRaw('UPPER(TRIM(tipo_pago)) <> ?', ['GARANTIA']);
            })
            ->sum('monto') ?? 0;

        return [
            'dias' => $dias,
            'base_total' => $subtotalRenta,
            'servicios_total' => $totalServicios,
            'subtotal' => $nuevoSubtotal,
            'iva' => $nuevoIva,
            'total' => $nuevoTotal,
            'pagos_realizados' => (float)$pagosRealizados,
            'saldo' => max(0, $nuevoTotal - (float)$pagosRealizados),
            'servicios' => $listaServicios,
        ];
    }

    /**
     * ⚙️ Actualiza servicios adicionales seleccionados.
     */
    public function actualizarServicios(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion'  => 'required|integer',
                'id_servicio'     => 'required|integer',
                'cantidad'        => 'required|integer|min:0',
                'precio_unitario' => 'required|numeric|min:0',
            ]);

            $idReservacion = $data['id_reservacion'];
            $idServicio = $data['id_servicio'];
            $cantidad = $data['cantidad'];
            $precioUnitario = $data['precio_unitario'];

            $servicioCatalogo = DB::table('servicios')->where('id_servicio', $idServicio)->first();
            $accion = '';

            if ($cantidad == 0) {
                DB::table('reservacion_servicio')
                    ->where('id_reservacion', $idReservacion)
                    ->where('id_servicio', $idServicio)
                    ->delete();

                $accion = 'deleted';

                $idContrato = DB::table('contratos')->where('id_reservacion', $idReservacion)->orderByDesc('id_contrato')->value('id_contrato');
                if ($idContrato && $servicioCatalogo && stripos($servicioCatalogo->nombre, 'conductor adicional') !== false) {
                    DB::table('contrato_conductor_adicional')->where('id_contrato', $idContrato)->delete();
                }
            } else {
                DB::table('reservacion_servicio')->updateOrInsert(
                    [
                        'id_reservacion' => $idReservacion,
                        'id_servicio'    => $idServicio
                    ],
                    [
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'updated_at'      => now(),
                    ]
                );

                $accion = 'saved';

                $idContrato = DB::table('contratos')->where('id_reservacion', $idReservacion)->orderByDesc('id_contrato')->value('id_contrato');
                if ($idContrato && $servicioCatalogo && stripos($servicioCatalogo->nombre, 'conductor adicional') !== false) {

                    $actualCount = DB::table('contrato_conductor_adicional')->where('id_contrato', $idContrato)->count();

                    if ($actualCount > $cantidad) {
                        DB::table('contrato_conductor_adicional')
                            ->where('id_contrato', $idContrato)
                            ->orderByDesc('id_conductor')
                            ->limit($actualCount - $cantidad)
                            ->delete();
                    } elseif ($actualCount < $cantidad) {
                        for ($i = $actualCount + 1; $i <= $cantidad; $i++) {
                            DB::table('contrato_conductor_adicional')->insert([
                                'id_contrato' => $idContrato,
                                'nombres'     => "Conductor adicional {$i}",
                                'apellidos'   => '',
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }
                }
            }

            $resumen = null;
            if (method_exists($this, 'recalcularResumenServiciosReservacion')) {
                $resumen = $this->recalcularResumenServiciosReservacion((int)$idReservacion);
            }

            return response()->json([
                'success'  => true,
                'status'   => $accion,
                'msg'      => 'Servicios y totales actualizados.',
                'detalles' => $resumen,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function guardarDeliveryReservacion(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion'      => 'required|integer|exists:reservaciones,id_reservacion',
                'delivery_activo'     => 'required|boolean',
                'delivery_ubicacion'  => 'nullable|string|max:120',
                'delivery_direccion'  => 'nullable|string|max:255',
                'delivery_km'         => 'nullable|numeric|min:0',
                'delivery_precio_km'  => 'nullable|numeric|min:0',
                'delivery_total'      => 'nullable|numeric|min:0',
            ]);

            $res = DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->first();

            if (!$res) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

            if ($data['delivery_activo'] == 0) {

                DB::table('reservaciones')
                    ->where('id_reservacion', $data['id_reservacion'])
                    ->update([
                        'delivery_activo'     => 0,
                        'delivery_ubicacion'  => null,
                        'delivery_direccion'  => null,
                        'delivery_km'         => 0,
                        'delivery_precio_km'  => 0,
                        'delivery_total'      => 0,
                        'updated_at'          => now(),
                    ]);

                return response()->json([
                    'status' => 'deleted',
                    'msg' => 'Delivery desactivado correctamente'
                ]);
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->update([
                    'delivery_activo'     => $data['delivery_activo'],
                    'delivery_ubicacion'  => $data['delivery_ubicacion'],
                    'delivery_direccion'  => $data['delivery_direccion'],
                    'delivery_km'         => $data['delivery_km'],
                    'delivery_precio_km'  => $data['delivery_precio_km'],
                    'delivery_total'      => $data['delivery_total'],
                    'updated_at'          => now(),
                ]);

            return response()->json([
                'status' => 'updated',
                'msg'    => 'Delivery guardado correctamente',
                'total'  => $data['delivery_total']
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en guardarDeliveryReservacion: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    /**
     * 🛡️ Actualiza paquete de seguro seleccionado.
     */
    public function actualizarSeguro(Request $request)
    {
        try {

            $esPaquete    = $request->filled('id_paquete');
            $esIndividual = $request->filled('id_seguro');

            if ($esPaquete === $esIndividual) {
                return response()->json([
                    'error' => 'Debe enviar paquete O seguro individual, no ambos.'
                ], 422);
            }

            $base = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            ]);

            $idReservacion = $base['id_reservacion'];

            if ($esPaquete) {

                $data = $request->validate([
                    'id_paquete'     => 'required|integer|exists:seguro_paquete,id_paquete',
                    'precio_por_dia' => 'required|numeric|min:0',
                ]);

                DB::table('reservacion_seguro_individual')
                    ->where('id_reservacion', $idReservacion)
                    ->delete();

                $existe = DB::table('reservacion_paquete_seguro')
                    ->where('id_reservacion', $idReservacion)
                    ->first();

                if ((float)$data['precio_por_dia'] === 0.0) {
                    if ($existe) {
                        DB::table('reservacion_paquete_seguro')
                            ->where('id', $existe->id)
                            ->delete();

                        return response()->json([
                            'status' => 'deleted',
                            'msg' => 'Paquete eliminado correctamente.'
                        ]);
                    }

                    return response()->json([
                        'status' => 'noop',
                        'msg' => 'No existía paquete para eliminar.'
                    ]);
                }

                if ($existe) {
                    DB::table('reservacion_paquete_seguro')
                        ->where('id', $existe->id)
                        ->update([
                            'id_paquete'     => $data['id_paquete'],
                            'precio_por_dia' => $data['precio_por_dia'],
                            'updated_at'     => now(),
                        ]);

                    return response()->json([
                        'status' => 'updated',
                        'msg' => 'Paquete actualizado correctamente.'
                    ]);
                }

                DB::table('reservacion_paquete_seguro')->insert([
                    'id_reservacion' => $idReservacion,
                    'id_paquete'     => $data['id_paquete'],
                    'precio_por_dia' => $data['precio_por_dia'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                return response()->json([
                    'status' => 'inserted',
                    'msg' => 'Paquete agregado correctamente.'
                ]);
            }

            if ($esIndividual) {

                $data = $request->validate([
                    'id_seguro' => 'required|integer|exists:seguros,id_seguro',
                    'precio'    => 'required|numeric|min:0',
                ]);

                DB::table('reservacion_paquete_seguro')
                    ->where('id_reservacion', $idReservacion)
                    ->delete();

                DB::table('reservacion_seguro_individual')->insert([
                    'id_reservacion' => $idReservacion,
                    'id_seguro'      => $data['id_seguro'],
                    'precio'         => $data['precio'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                return response()->json([
                    'status' => 'inserted',
                    'msg' => 'Seguro individual agregado correctamente.'
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error en actualizarSeguro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno al actualizar el seguro.'
            ], 500);
        }
    }

    public function actualizarSegurosIndividuales(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_seguro'      => 'required|integer|exists:seguro_individuales,id_individual',
                'precio_por_dia' => 'required|numeric|min:0',
            ]);

            $idReservacion = $data['id_reservacion'];
            $idIndividual  = $data['id_seguro'];

            DB::table('reservacion_paquete_seguro')
                ->where('id_reservacion', $idReservacion)
                ->delete();

            $existe = DB::table('reservacion_seguro_individual')
                ->where('id_reservacion', $idReservacion)
                ->where('id_individual', $idIndividual)
                ->first();

            if (!$existe) {
                DB::table('reservacion_seguro_individual')->insert([
                    'id_reservacion' => $idReservacion,
                    'id_individual'  => $idIndividual,
                    'precio_por_dia' => $data['precio_por_dia'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            return response()->json([
                'ok' => true,
                'status' => 'inserted',
                'msg' => 'Protección individual agregada correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en actualizarSegurosIndividuales: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar.'], 500);
        }
    }

    public function eliminarSeguroIndividual(Request $request)
    {
        try {
            $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_seguro'      => 'required|integer|exists:seguro_individuales,id_individual',
            ]);

            DB::table('reservacion_seguro_individual')
                ->where('id_reservacion', $request->id_reservacion)
                ->where('id_individual', $request->id_seguro)
                ->delete();

            return response()->json([
                'ok' => true,
                'status' => 'deleted',
                'msg' => 'Protección individual eliminada.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al eliminar seguro individual: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al eliminar.'], 500);
        }
    }

    public function eliminarTodosLosIndividuales(Request $request)
    {
        try {
            $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            ]);

            DB::table('reservacion_seguro_individual')
                ->where('id_reservacion', $request->id_reservacion)
                ->delete();

            return response()->json([
                'ok' => true,
                'msg' => 'Todos los seguros individuales eliminados.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al borrar todos los individuales: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function obtenerOfertaUpgrade($idReservacion)
    {
        try {
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$res) {
                return response()->json(['success' => false, 'error' => 'Reservación no encontrada']);
            }

            $catActual = DB::table('categorias_carros')
                ->where('id_categoria', $res->id_categoria)
                ->first();

            if (!$catActual) {
                return response()->json(['success' => false, 'error' => 'Categoría actual no encontrada']);
            }

            $orden = ["C", "D", "E", "F", "IC", "I", "IB", "M", "L", "H", "HI"];

            $posActual = array_search($catActual->codigo, $orden);

            if ($posActual === false) {
                return response()->json(['success' => false, 'msg' => 'Categoría actual no está en el orden oficial.']);
            }

            $codigosSuperiores = array_slice($orden, $posActual + 1);

            if (empty($codigosSuperiores)) {
                return response()->json(['success' => false, 'msg' => 'No hay categorías superiores disponibles.']);
            }

            $categorias = DB::table('categorias_carros')
                ->whereIn('codigo', $codigosSuperiores)
                ->orderBy('precio_dia', 'asc')
                ->get();

            if ($categorias->isEmpty()) {
                return response()->json(['success' => false, 'msg' => 'No hay categorías superiores en DB.']);
            }

            $catSuperior = $categorias->random();

            $vehiculo = DB::table('vehiculos')
                ->where('id_categoria', $catSuperior->id_categoria)
                ->inRandomOrder()
                ->first();

            if (!$vehiculo) {
                return response()->json(['success' => false, 'msg' => 'No hay vehículos disponibles para upgrade.']);
            }

            $foto = DB::table('vehiculo_imagenes')
                ->where('id_vehiculo', $vehiculo->id_vehiculo)
                ->orderBy('orden', 'asc')
                ->value('url');

            $fotoFinal = $foto ?? '/img/default-car.jpg';

            $precioReal    = $catSuperior->precio_dia;
            $precioInflado = round($precioReal * 1.35, 2);
            $descuento     = rand(55, 75);
            $precioFinal   = round($precioInflado * (1 - ($descuento / 100)), 2);

            return response()->json([
                'success' => true,
                'categoria' => [
                    'id_categoria'     => $catSuperior->id_categoria,
                    'codigo'           => $catSuperior->codigo,
                    'nombre'           => $catSuperior->nombre,
                    'descripcion'      => $catSuperior->descripcion,

                    'precio_real'      => $precioReal,
                    'precio_inflado'   => $precioInflado,
                    'descuento'        => $descuento,
                    'precio_final'     => $precioFinal,

                    'imagen'           => $fotoFinal,
                    'nombre_vehiculo'  => $vehiculo->nombre_publico,
                    'transmision'      => $vehiculo->transmision,
                    'asientos'         => $vehiculo->asientos,
                    'puertas'          => $vehiculo->puertas,
                    'color'            => $vehiculo->color,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Error obtenerOfertaUpgrade: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error interno'], 500);
        }
    }
}
