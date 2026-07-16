<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservacionUsuarioMail;

class VisorReservacionController extends Controller
{
    /* =========================================================
       GET ÚNICO – MUESTRA LAS 3 CARDS
    ========================================================= */
    public function mostrarReservacion($id)
    {
        // ---------- CARD 1 ----------
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select(
                'id_reservacion',
                'id_categoria',
                'fecha_inicio',
                'fecha_fin',
                'hora_retiro',
                'hora_entrega'
            )
            ->first();

        if (!$reservacion) {
            abort(404, 'Reservación no encontrada');
        }

        // Servicios asociados
        $servicios = DB::table('reservacion_servicio')
            ->join('servicios', 'reservacion_servicio.id_servicio', '=', 'servicios.id_servicio')
            ->where('reservacion_servicio.id_reservacion', $id)
            ->select(
                'servicios.id_servicio',
                'servicios.nombre',
                'servicios.tipo_cobro',
                'servicios.usuario',
                'reservacion_servicio.cantidad',
                'reservacion_servicio.precio_unitario'
            )
            ->get();

        // Totales reales
        $subtotalServicios = 0;
        foreach ($servicios as $s) {
            $subtotalServicios += $s->cantidad * $s->precio_unitario;
        }

        // Obtener tarifa diaria de la categoría actual
        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $reservacion->id_categoria)
            ->select('precio_dia')
            ->first();

        // El multiplicador ×1.25 solo aplica si el pago es en mostrador.
        // Si es pago en línea (prepago), se usa el precio base sin multiplicador.
        $metodoPagoRes = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->value('metodo_pago');

        $multiplicador = ($metodoPagoRes === 'mostrador') ? 1.25 : 1.0;
        $precioDiaCategoria = ($categoria->precio_dia ?? 0) * $multiplicador;

        // Calcular duración
        $inicio = Carbon::parse(
            $reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00:00')
        );

        $fin = Carbon::parse(
            $reservacion->fecha_fin . ' ' . ($reservacion->hora_entrega ?? '00:00:00')
        );

        // Días con tolerancia de 1 hora (misma fórmula que admin y que al crear):
        // si el exceso sobre las 24h es de MÁS de 1 hora, suma un día.
        $horasTotales = $inicio->lt($fin) ? $inicio->diffInHours($fin) : 0;
        $diasBase     = intdiv($horasTotales, 24);
        $horasExtra   = $horasTotales % 24;
        $dias         = ($horasExtra > 1) ? $diasBase + 1 : max(1, $diasBase);

        $baseCategoria = $precioDiaCategoria * $dias;

        $subtotal = $baseCategoria + $subtotalServicios;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        // Catálogo de servicios: los visibles para el cliente (usuario = 1).
        // Los automáticos (dropoff = 11, conductor menor = 5) están en usuario = 0,
        // por lo que quedan excluidos solos y se agregan por lógica de negocio.
        $catalogoServicios = DB::table('servicios')
            ->where('activo', 1)
            ->where('usuario', 1)
            ->orderBy('nombre')
            ->get();

        // Categorías para el modal dinámico
$categoriasCards = DB::table('categorias_carros')
    ->where('activo', 1)
    ->select(
        'id_categoria',
        'codigo',
        'nombre',
        'descripcion',
        'precio_dia',
        'descuento_miembro'
    )
    ->orderBy('codigo')
    ->get();

        // ---------- CARD 2 ----------
        $cliente = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select(
                'nombre_cliente',
                'email_cliente',
                'telefono_cliente'
            )
            ->first();

        // ---------- CARD 3 ----------
        $itinerario = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select(
                'fecha_inicio',
                'fecha_fin',
                'hora_retiro',
                'hora_entrega',
                'sucursal_retiro',
                'sucursal_entrega'
            )
            ->first();

        $sucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.activo', 1)
            ->select(
                's.id_sucursal',
                's.nombre as nombre_sucursal',
                'c.estado as estado'
            )
            ->orderBy('c.estado')
            ->orderBy('s.nombre')
            ->get();

        // Agrupar por estado para los <optgroup> (pick-up y devolución)
        $sucursalesPorEstado = $sucursales->groupBy('estado');

        // ---------- CONTRATO ----------
        $tieneContrato = DB::table('contratos')
            ->where('id_reservacion', $id)
            ->exists();

        return view('Usuarios.visorReservacion', [
            // Card 1
            'reservacion'       => $reservacion,
            'servicios'         => $servicios,
            'subtotal'          => $subtotal,
            'iva'               => $iva,
            'total'             => $total,
            'baseCategoria'     => $baseCategoria,
            'dias'              => $dias,
            'catalogoServicios' => $catalogoServicios,
            'categoriasCards'   => $categoriasCards,

            // Card 2
            'cliente'           => $cliente,

            // Card 3
            'itinerario'        => $itinerario,
            'sucursales'        => $sucursales,
            'sucursalesPorEstado' => $sucursalesPorEstado,

            'tieneContrato'     => $tieneContrato,
        ]);
    }

    /* =========================================================
       PUT ÚNICO – DECIDE QUÉ CARD ACTUALIZAR
    ========================================================= */
    public function actualizarReservacion(Request $request, $id)
    {
        // 🔒 BLOQUEO POR CONTRATO
        $existeContrato = DB::table('contratos')
            ->where('id_reservacion', $id)
            ->exists();

        if ($existeContrato) {
            return back()->with('error', 'No se puede editar la reservación porque ya tiene contrato');
        }

        switch ($request->card) {
            case 'card1':
                return $this->actualizarCard1($request, $id);
            case 'card2':
                return $this->actualizarCard2($request, $id);
            case 'card3':
                return $this->actualizarCard3($request, $id);
            default:
                return back()->with('error', 'Acción no válida');
        }
    }

    /* =========================================================
       CARD 1 – VEHÍCULO / SERVICIOS
    ========================================================= */
    private function actualizarCard1(Request $request, $id)
    {
        $request->validate([
            'id_categoria'         => 'required|integer',
            'servicios'            => 'nullable|array',
            'servicios.*.id'       => 'required|integer',
            'servicios.*.cantidad' => 'required|integer|min:1',
            // 'precio' ya NO se valida ni se usa: el precio real se lee de BD.
            'servicios.*.precio'   => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Obtener datos de la reservación
            $reservacionActual = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->select(
                    'fecha_inicio',
                    'fecha_fin',
                    'hora_retiro',
                    'hora_entrega'
                )
                ->first();

            if (!$reservacionActual) {
                DB::rollBack();
                return back()->with('error', 'Reservación no encontrada');
            }

            // Obtener tarifa diaria de la nueva categoría
            $categoriaNueva = DB::table('categorias_carros')
                ->where('id_categoria', $request->id_categoria)
                ->select('precio_dia')
                ->first();

            if (!$categoriaNueva) {
                DB::rollBack();
                return back()->with('error', 'La categoría seleccionada no existe');
            }

            // El multiplicador ×1.25 solo aplica si el pago es en mostrador
            $metodoPagoRes = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->value('metodo_pago');

            $multiplicador = ($metodoPagoRes === 'mostrador') ? 1.25 : 1.0;
            $precioDiaCategoria = ($categoriaNueva->precio_dia ?? 0) * $multiplicador;

            // Calcular duración
            $inicio = Carbon::parse(
                $reservacionActual->fecha_inicio . ' ' . ($reservacionActual->hora_retiro ?? '00:00:00')
            );

            $fin = Carbon::parse(
                $reservacionActual->fecha_fin . ' ' . ($reservacionActual->hora_entrega ?? '00:00:00')
            );

            $minutos = $inicio->lt($fin) ? $inicio->diffInMinutes($fin) : 0;
            $dias = max(1, (int) ceil($minutos / 1440));

            // Base real según la nueva categoría
            $baseCategoria = $precioDiaCategoria * $dias;

            // Actualizar categoría
            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'id_categoria' => $request->id_categoria,
                    'tarifa_base'  => $precioDiaCategoria,
                    'updated_at'   => now(),
                ]);

            // Eliminar servicios anteriores, PRESERVANDO los automáticos:
            //  - 11 (Drop Off): lo regenera recalcularDropoff() más abajo.
            //  - 5  (Conductor menor): NO se recalcula en la web de usuario
            //    (esa lógica depende de la edad y vive en el flujo del agente).
            //    Se preserva tal cual estaba para no perderlo al editar.
            DB::table('reservacion_servicio')
                ->where('id_reservacion', $id)
                ->whereNotIn('id_servicio', [5, 11])
                ->delete();

            // Insertar servicios nuevos si existen.
            // El precio y el tipo de cobro SIEMPRE se leen de la tabla `servicios`
            // (nunca del front). La gasolina (id 1) resuelve litros por categoría.
            $subtotalServicios = 0;

            if (!empty($request->servicios)) {
                foreach ($request->servicios as $s) {
                    // El usuario nunca manda 5 ni 11; si llegaran, se ignoran
                    // porque son automáticos y ya se manejan aparte.
                    if (in_array((int) $s['id'], [5, 11], true)) {
                        continue;
                    }

                    // Precio real desde BD (fuente de verdad)
                    $servicioDb = DB::table('servicios')
                        ->where('id_servicio', $s['id'])
                        ->where('activo', 1)
                        ->first();

                    if (!$servicioDb) {
                        continue; // servicio inexistente o inactivo: se omite
                    }

                    $cantidad = (int) $s['cantidad'];
                    $precioUnitario = (float) $servicioDb->precio;

                    // Caso especial GASOLINA (por_tanque): cantidad = litros del
                    // tanque de la categoría, precio_unitario = 20 (igual que admin).
                    if ((int) $s['id'] === 1) {
                        $capacidadTanque = DB::table('vehiculos')
                            ->where('id_categoria', $request->id_categoria)
                            ->max('capacidad_tanque') ?? 0;

                        $cantidad = (int) ($capacidadTanque ?: 1);
                        $precioUnitario = 20.00;
                    }

                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $id,
                        'id_servicio'     => $servicioDb->id_servicio,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }

            // Guardar la tarifa base (con ×1.25 ya aplicado)
            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'tarifa_base' => $precioDiaCategoria,
                    'updated_at'  => now(),
                ]);

            // Recalcular dropoff (por si cambió la categoría, cambia el costo_km)
            // y luego los totales con todo incluido
            $this->recalcularDropoff($id);
            $this->recalcularTotalesReserva($id);

            DB::commit();

            return back()->with('success', 'Vehículo y servicios actualizados');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar Card 1');
        }
    }

    /* =========================================================
       CARD 2 – DATOS DEL CLIENTE
    ========================================================= */
    private function actualizarCard2(Request $request, $id)
    {
        $request->validate([
            'nombre_cliente'    => 'required|string|max:100',
            'email_cliente'     => 'required|email',
            'telefono_cliente'  => 'required|string|max:20',
        ]);

        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'nombre_cliente'    => $request->nombre_cliente,
                'email_cliente'     => $request->email_cliente,
                'telefono_cliente'  => $request->telefono_cliente,
                'updated_at'        => now(),
            ]);

        return back()->with('success', 'Datos del cliente actualizados');
    }

    /* =========================================================
       CARD 3 – FECHAS, HORAS Y SUCURSALES
    ========================================================= */
    private function actualizarCard3(Request $request, $id)
    {
        $request->validate([
            'fecha_inicio'     => 'required|date',
            'fecha_fin'        => 'required|date|after_or_equal:fecha_inicio',
            'hora_retiro'      => 'required',
            'hora_entrega'     => 'required',
            'sucursal_retiro'  => 'required|integer',
            'sucursal_entrega' => 'required|integer',
        ]);

        // Validación de horas si la fecha es la misma
        if ($request->fecha_inicio === $request->fecha_fin) {
            if ($request->hora_entrega <= $request->hora_retiro) {
                return back()->withErrors([
                    'hora_entrega' => 'La hora de entrega debe ser posterior a la de retiro',
                ]);
            }
        }

        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'fecha_inicio'     => $request->fecha_inicio,
                'fecha_fin'        => $request->fecha_fin,
                'hora_retiro'      => $request->hora_retiro,
                'hora_entrega'     => $request->hora_entrega,
                'sucursal_retiro'  => $request->sucursal_retiro,
                'sucursal_entrega' => $request->sucursal_entrega,
                'updated_at'       => now(),
            ]);

        // Recalcular dropoff (según nuevas sucursales) y totales
        $this->recalcularDropoff($id);
        $this->recalcularTotalesReserva($id);

        return back()->with('success', 'Fechas y sucursales actualizadas');
    }

    /* =========================================================
       DROPOFF – recalcula el cargo por devolver en otra ciudad
       Se llama al guardar Card 1 o Card 3. Consistente con reservar:
       si la sucursal de entrega está fuera de Querétaro, agrega el
       servicio id 11 (Drop Off) con km × costo_km. Si es misma ciudad,
       lo elimina.
    ========================================================= */
    private function recalcularDropoff($id)
    {
        $DROPOFF_ID = 11;

        $reserva = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select('id_categoria', 'sucursal_retiro', 'sucursal_entrega')
            ->first();

        if (!$reserva) {
            return;
        }

        // Siempre quitar el dropoff anterior para recalcular limpio
        DB::table('reservacion_servicio')
            ->where('id_reservacion', $id)
            ->where('id_servicio', $DROPOFF_ID)
            ->delete();

        // Si no hay sucursal de entrega, o es la misma que la de retiro, no hay dropoff
        if (empty($reserva->sucursal_entrega) || $reserva->sucursal_entrega == $reserva->sucursal_retiro) {
            return;
        }

        // Nombre de la sucursal de entrega
        $sucEntrega = DB::table('sucursales')
            ->where('id_sucursal', $reserva->sucursal_entrega)
            ->select('nombre')
            ->first();

        if (!$sucEntrega) {
            return;
        }

        // Buscar los km del destino en ubicaciones_servicio (las de Querétaro no están ahí)
        $km = DB::table('ubicaciones_servicio')
            ->where('destino', $sucEntrega->nombre)
            ->where('activo', 1)
            ->value('km');

        // Si no hay km (destino en Querétaro o no listado), no hay dropoff
        if (!$km || $km <= 0) {
            return;
        }

        // Costo por km de la categoría
        $costoKm = DB::table('categoria_costo_km')
            ->where('id_categoria', $reserva->id_categoria)
            ->where('activo', 1)
            ->value('costo_km');

        if (!$costoKm || $costoKm <= 0) {
            return;
        }

        $cargoDropoff = round($km * $costoKm, 2);

        // Insertar el dropoff como servicio id 11 (cantidad 1, precio = cargo total)
        DB::table('reservacion_servicio')->insert([
            'id_reservacion'  => $id,
            'id_servicio'     => $DROPOFF_ID,
            'cantidad'        => 1,
            'precio_unitario' => $cargoDropoff,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    /* =========================================================
       RECALCULA subtotal/iva/total de la reserva desde la BD
       (tarifa base con ×1.25 + todos los servicios, incluido dropoff)
    ========================================================= */
    private function recalcularTotalesReserva($id)
    {
        $reserva = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select('id_categoria', 'fecha_inicio', 'fecha_fin', 'hora_retiro', 'hora_entrega')
            ->first();

        if (!$reserva) {
            return;
        }

        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $reserva->id_categoria)
            ->select('precio_dia')
            ->first();

        // El multiplicador ×1.25 solo aplica si el pago es en mostrador
        $metodoPagoRes = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->value('metodo_pago');

        $multiplicador = ($metodoPagoRes === 'mostrador') ? 1.25 : 1.0;
        $precioDia = ($categoria->precio_dia ?? 0) * $multiplicador;

        $inicio = Carbon::parse($reserva->fecha_inicio . ' ' . ($reserva->hora_retiro ?? '00:00:00'));
        $fin    = Carbon::parse($reserva->fecha_fin . ' ' . ($reserva->hora_entrega ?? '00:00:00'));

        // Días con tolerancia de 1 hora (misma fórmula que admin y que al crear).
        $horasTotales = $inicio->lt($fin) ? $inicio->diffInHours($fin) : 0;
        $diasBase     = intdiv($horasTotales, 24);
        $horasExtra   = $horasTotales % 24;
        $dias         = ($horasExtra > 1) ? $diasBase + 1 : max(1, $diasBase);

        $baseCategoria = $precioDia * $dias;

        // Subtotal de servicios respetando tipo_cobro:
        //  - por_dia   → cantidad × precio_unitario × días
        //  - por_evento→ cantidad × precio_unitario
        //  - por_tanque→ cantidad × precio_unitario (gasolina: litros × 20)
        // El dropoff (id 11) se guarda como cargo fijo (cantidad 1, precio total),
        // por lo que su tipo_cobro no debe ser por_dia y cae en × 1 automáticamente.
        $subtotalServicios = 0;

        $serviciosReserva = DB::table('reservacion_servicio as rs')
            ->join('servicios as s', 's.id_servicio', '=', 'rs.id_servicio')
            ->where('rs.id_reservacion', $id)
            ->select('rs.cantidad', 'rs.precio_unitario', 's.tipo_cobro')
            ->get();

        foreach ($serviciosReserva as $s) {
            $lineaBase = (float) $s->cantidad * (float) $s->precio_unitario;
            $subtotalServicios += ($s->tipo_cobro === 'por_dia')
                ? $lineaBase * $dias
                : $lineaBase;
        }

        $subtotal = $baseCategoria + $subtotalServicios;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'subtotal'   => $subtotal,
                'impuestos'  => $iva,
                'total'      => $total,
                'updated_at' => now(),
            ]);
    }

    /* =========================================================
       MODIFICAR RESERVACIÓN – AGENTE IA
       Endpoint delgado: valida token + bloqueos (1h, contrato) y
       reutiliza la lógica de la vista de usuario (updates + dropoff +
       recálculo de totales + reenvío de correo). No tiene lógica propia
       de cálculo. Recibe cualquier combinación de cambios (todos opcionales).
    ========================================================= */
    public function modificarAgente(Request $request)
    {
        // 1. Validar token del agente
        $tokenRecibido = $request->header('X-Agente-Token');
        $tokenEsperado = env('AGENTE_API_TOKEN');

        if (!$tokenEsperado || $tokenRecibido !== $tokenEsperado) {
            return response()->json(['ok' => false, 'message' => 'No autorizado.'], 401);
        }

        // 2. Validar datos (todos opcionales excepto el código)
        $validated = $request->validate([
            'codigo'            => 'required|string',
            'id_categoria'      => 'nullable|integer|exists:categorias_carros,id_categoria',
            'fecha_inicio'      => 'nullable|date',
            'fecha_fin'         => 'nullable|date',
            'hora_retiro'       => 'nullable',
            'hora_entrega'      => 'nullable',
            'sucursal_retiro'   => 'nullable|integer',
            'sucursal_entrega'  => 'nullable|integer',
            'nombre_cliente'    => 'nullable|string|max:120',
            'email_cliente'     => 'nullable|email|max:120',
            'telefono_cliente'  => 'nullable|string|max:40',
            'servicios'         => 'nullable|array',
            'servicios.*.id'        => 'required_with:servicios|integer',
            'servicios.*.cantidad'  => 'required_with:servicios|integer|min:1',
            'servicios.*.precio'    => 'required_with:servicios|numeric|min:0',
            'idioma'            => 'nullable|string|in:es,en',
        ]);

        // Aplicar el idioma del cliente para el correo (por defecto español)
        app()->setLocale($validated['idioma'] ?? 'es');

        // 3. Buscar la reserva por código
        $reserva = DB::table('reservaciones')
            ->where('codigo', trim($validated['codigo']))
            ->first();

        if (!$reserva) {
            return response()->json(['ok' => false, 'message' => 'Reservación no encontrada.'], 404);
        }

        $id = $reserva->id_reservacion;

        // 4. BLOQUEO por contrato
        $tieneContrato = DB::table('contratos')->where('id_reservacion', $id)->exists();
        if ($tieneContrato) {
            return response()->json([
                'ok' => false,
                'message' => 'La reservación ya tiene contrato y no se puede modificar.',
            ], 409);
        }

        // 5. BLOQUEO por ventana de 1 hora antes del pickup
        $pickup = Carbon::parse($reserva->fecha_inicio . ' ' . ($reserva->hora_retiro ?? '00:00:00'));
        if ($pickup->diffInMinutes(now(), false) > -60) {
            return response()->json([
                'ok' => false,
                'message' => 'La reservación solo se puede modificar hasta 1 hora antes del pickup.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            // 6. Actualizar CATEGORÍA (si viene)
            if (!empty($validated['id_categoria'])) {
                DB::table('reservaciones')->where('id_reservacion', $id)->update([
                    'id_categoria' => $validated['id_categoria'],
                    'updated_at'   => now(),
                ]);
            }

            // 7. Actualizar FECHAS / HORAS / SUCURSALES (los que vengan)
            $updateItinerario = [];
            foreach (['fecha_inicio', 'fecha_fin', 'hora_retiro', 'hora_entrega', 'sucursal_retiro', 'sucursal_entrega'] as $campo) {
                if (isset($validated[$campo]) && $validated[$campo] !== null) {
                    $updateItinerario[$campo] = $validated[$campo];
                }
            }
            if (!empty($updateItinerario)) {
                $updateItinerario['updated_at'] = now();
                DB::table('reservaciones')->where('id_reservacion', $id)->update($updateItinerario);
            }

            // 8. Actualizar DATOS DEL CLIENTE (los que vengan)
            $updateCliente = [];
            foreach (['nombre_cliente', 'email_cliente', 'telefono_cliente'] as $campo) {
                if (isset($validated[$campo]) && $validated[$campo] !== null) {
                    $updateCliente[$campo] = $validated[$campo];
                }
            }
            if (!empty($updateCliente)) {
                $updateCliente['updated_at'] = now();
                DB::table('reservaciones')->where('id_reservacion', $id)->update($updateCliente);
            }

            // 9. Actualizar SERVICIOS (si vienen, reemplaza la lista completa)
            //    OJO: no tocamos el dropoff aquí, lo recalcula recalcularDropoff después.
            if ($request->has('servicios')) {
                // Borrar servicios, PRESERVANDO los automáticos:
                //  - 11 (Drop Off): lo regenera recalcularDropoff() después.
                //  - 5  (Conductor menor): lo RECALCULA el agente por edad
                //    (esa lógica la agrega el flujo del agente / Python).
                //    Aquí lo preservamos para que ese recálculo trabaje sobre
                //    la lista ya depurada, sin perderlo ni duplicarlo.
                DB::table('reservacion_servicio')
                    ->where('id_reservacion', $id)
                    ->whereNotIn('id_servicio', [5, 11])
                    ->delete();

                if (!empty($validated['servicios'])) {
                    foreach ($validated['servicios'] as $s) {
                        // 5 y 11 son automáticos: no se insertan desde la lista.
                        if (in_array((int) $s['id'], [5, 11], true)) {
                            continue;
                        }

                        // Precio real desde BD (fuente de verdad)
                        $servicioDb = DB::table('servicios')
                            ->where('id_servicio', $s['id'])
                            ->where('activo', 1)
                            ->first();

                        if (!$servicioDb) {
                            continue;
                        }

                        $cantidad = (int) $s['cantidad'];
                        $precioUnitario = (float) $servicioDb->precio;

                        // Caso especial GASOLINA (por_tanque): litros × 20.
                        if ((int) $s['id'] === 1) {
                            $reservaCatId = DB::table('reservaciones')
                                ->where('id_reservacion', $id)
                                ->value('id_categoria');

                            $capacidadTanque = DB::table('vehiculos')
                                ->where('id_categoria', $reservaCatId)
                                ->max('capacidad_tanque') ?? 0;

                            $cantidad = (int) ($capacidadTanque ?: 1);
                            $precioUnitario = 20.00;
                        }

                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => $servicioDb->id_servicio,
                            'cantidad'        => $cantidad,
                            'precio_unitario' => $precioUnitario,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }

            // 9-BIS. Recalcular CONDUCTOR MENOR (id 5) según la edad al pickup final.
            //   El conductor menor aplica solo a titulares de 18 a 24 años.
            //   Como las fechas pueden haber cambiado en esta modificación, se
            //   recalcula la edad al pickup FINAL y se decide si el cargo debe
            //   estar o no. La fecha de nacimiento vive en la propia reserva.
            $reservaCM = DB::table('reservaciones')->where('id_reservacion', $id)
                ->select('fecha_inicio', 'fecha_nacimiento')->first();

            // Sólo se puede recalcular si tenemos la fecha de nacimiento guardada.
            if ($reservaCM && !empty($reservaCM->fecha_nacimiento)) {
                $pickupFinal = Carbon::parse($reservaCM->fecha_inicio);
                $nacimiento  = Carbon::parse($reservaCM->fecha_nacimiento);
                $edad        = $nacimiento->diffInYears($pickupFinal);

                $aplicaConductorMenor = ($edad >= 18 && $edad <= 24);

                $tieneConductorMenor = DB::table('reservacion_servicio')
                    ->where('id_reservacion', $id)
                    ->where('id_servicio', 5)
                    ->exists();

                if ($aplicaConductorMenor && !$tieneConductorMenor) {
                    // Debe estar y no está: agregarlo con su precio de BD.
                    $servicioCM = DB::table('servicios')
                        ->where('id_servicio', 5)
                        ->where('activo', 1)
                        ->first();

                    if ($servicioCM) {
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $id,
                            'id_servicio'     => 5,
                            'cantidad'        => 1,
                            'precio_unitario' => (float) $servicioCM->precio,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                } elseif (!$aplicaConductorMenor && $tieneConductorMenor) {
                    // Ya no aplica (p. ej. cumplió 25 con las fechas nuevas): quitarlo.
                    DB::table('reservacion_servicio')
                        ->where('id_reservacion', $id)
                        ->where('id_servicio', 5)
                        ->delete();
                }
                // Si aplica y ya está, o no aplica y no está: no se hace nada.
                // El precio por días lo recalcula recalcularTotalesReserva más abajo.
            }

            // 10. Guardar tarifa_base actualizada (con ×1.25 condicional según método de pago)
            $reservaAct = DB::table('reservaciones')->where('id_reservacion', $id)
                ->select('id_categoria', 'metodo_pago')->first();
            $catAct = DB::table('categorias_carros')->where('id_categoria', $reservaAct->id_categoria)
                ->value('precio_dia');
            $mult = ($reservaAct->metodo_pago === 'mostrador') ? 1.25 : 1.0;
            DB::table('reservaciones')->where('id_reservacion', $id)->update([
                'tarifa_base' => ($catAct ?? 0) * $mult,
                'updated_at'  => now(),
            ]);

            // 11. Recalcular DROPOFF y TOTALES (reutiliza la lógica de la vista)
            $this->recalcularDropoff($id);
            $this->recalcularTotalesReserva($id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Error al modificar la reservación.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // 12. Reenviar el correo con los datos actualizados (lógica pura, sin redirect)
        $this->enviarCorreoReservacion($id);

        // 13. Devolver el nuevo estado
        $final = DB::table('reservaciones')->where('id_reservacion', $id)
            ->select('codigo', 'subtotal', 'impuestos', 'total', 'estado')->first();

        return response()->json([
            'ok'        => true,
            'folio'     => $final->codigo,
            'subtotal'  => $final->subtotal,
            'impuestos' => $final->impuestos,
            'total'     => $final->total,
            'estado'    => $final->estado,
            'message'   => 'Reservación modificada y correo reenviado.',
        ]);
    }


    /* =========================================================
       ENVÍA el correo de la reservación (lógica pura, sin redirect).
       La usan tanto reenviarCorreo (web) como modificarAgente (agente).
       Devuelve true si se envió, false si no.
    ========================================================= */
    private function enviarCorreoReservacion($id)
    {
        // 1️⃣ Obtener reservación
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!$reservacion) {
            return false;
        }

        // 2️⃣ Obtener categoría
        $categoria = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
            ->where('id_categoria', $reservacion->id_categoria)
            ->first();

        // 3️⃣ Ficha "Tu Auto"
        $predeterminados = [
            'C'  => ['pax'=>5,'small'=>2,'big'=>1],
            'D'  => ['pax'=>5,'small'=>2,'big'=>1],
            'E'  => ['pax'=>5,'small'=>2,'big'=>2],
            'F'  => ['pax'=>5,'small'=>2,'big'=>2],
            'IC' => ['pax'=>5,'small'=>2,'big'=>2],
            'I'  => ['pax'=>5,'small'=>3,'big'=>2],
            'IB' => ['pax'=>7,'small'=>3,'big'=>2],
            'M'  => ['pax'=>7,'small'=>4,'big'=>2],
            'L'  => ['pax'=>13,'small'=>4,'big'=>3],
            'H'  => ['pax'=>5,'small'=>3,'big'=>2],
            'HI' => ['pax'=>5,'small'=>3,'big'=>2],
        ];

        $codigoCat = strtoupper(trim((string)($categoria->codigo ?? '')));
        $cap = $predeterminados[$codigoCat] ?? ['pax'=>5,'small'=>2,'big'=>1];

        $nombreCat = trim((string)($categoria->nombre ?? ''));
        $singular = $nombreCat;

        if (mb_substr($singular,-1) === 's') {
            $singular = mb_substr($singular,0,mb_strlen($singular)-1);
        }

        $singular = mb_strtoupper($singular);

        $tituloAuto = trim((string)($categoria->descripcion ?? 'Auto o similar'));
        $subtituloAuto = $singular . " | CATEGORÍA " . ($codigoCat ?: '-');

        $tuAuto = [
            'titulo'     => $tituloAuto,
            'subtitulo'  => $subtituloAuto,
            'pax'        => (int)$cap['pax'],
            'small'      => (int)$cap['small'],
            'big'        => (int)$cap['big'],
            'transmision'=> 'Transmisión manual o automática',
            'tech'       => 'Apple CarPlay | Android Auto',
            'incluye'    => 'KM ilimitados | Reelevo de Responsabilidad (LI)',
        ];

        // 4️⃣ Servicios / extras
        // Días de la renta (para cobrar por_dia × días en el desglose del correo).
        $inicioCorreo = Carbon::parse(
            $reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00:00')
        );
        $finCorreo = Carbon::parse(
            $reservacion->fecha_fin . ' ' . ($reservacion->hora_entrega ?? '00:00:00')
        );
        // Días con tolerancia de 1 hora (misma fórmula que admin y que al crear).
        $horasCorreo = $inicioCorreo->lt($finCorreo) ? $inicioCorreo->diffInHours($finCorreo) : 0;
        $diasBaseCorreo = intdiv($horasCorreo, 24);
        $horasExtraCorreo = $horasCorreo % 24;
        $diasCorreo = ($horasExtraCorreo > 1) ? $diasBaseCorreo + 1 : max(1, $diasBaseCorreo);
        $extrasReserva = DB::table('reservacion_servicio as rs')
            ->join('servicios as s','s.id_servicio','=','rs.id_servicio')
            ->where('rs.id_reservacion',$id)
            ->select(
                's.id_servicio',
                's.nombre',
                's.descripcion',
                's.tipo_cobro',
                'rs.cantidad',
                'rs.precio_unitario',
                // 'total' = cobro real de la línea: por_dia se multiplica por días,
                // los demás (por_evento / por_tanque / dropoff) quedan × 1.
                DB::raw("(rs.cantidad * rs.precio_unitario * CASE WHEN s.tipo_cobro = 'por_dia' THEN {$diasCorreo} ELSE 1 END) as total")
            )
            ->get();

        // 5️⃣ Lugar retiro / entrega
        $retiroInfo = DB::table('sucursales as s')
            ->join('ciudades as c','c.id_ciudad','=','s.id_ciudad')
            ->where('s.id_sucursal',$reservacion->sucursal_retiro)
            ->select('s.nombre as sucursal','c.nombre as ciudad')
            ->first();

        $entregaInfo = DB::table('sucursales as s')
            ->join('ciudades as c','c.id_ciudad','=','s.id_ciudad')
            ->where('s.id_sucursal',$reservacion->sucursal_entrega)
            ->select('s.nombre as sucursal','c.nombre as ciudad')
            ->first();

        $lugarRetiro  = $retiroInfo ? ($retiroInfo->ciudad.' - '.$retiroInfo->sucursal) : '-';
        $lugarEntrega = $entregaInfo ? ($entregaInfo->ciudad.' - '.$entregaInfo->sucursal) : '-';

        // 6️⃣ Imagen categoría
        $catImages = [
            1=>'img/aveo.png',
            2=>'img/virtus.png',
            3=>'img/jetta.png',
            4=>'img/camry.png',
            5=>'img/renegade.png',
            6=>'img/taos.png',
            7=>'img/avanza.png',
            8=>'img/Odyssey.png',
            9=>'img/Hiace.png',
            10=>'img/Frontier.png',
            11=>'img/Tacoma.png',
        ];

        $catId = (int)($categoria->id_categoria ?? 0);
        $baseUrl = rtrim(config('app.url'),'/');

        $imgPath = $catImages[$catId] ?? 'img/categorias/placeholder.png';

        $imgCategoria = $baseUrl.'/'.ltrim($imgPath,'/');

        // 7️⃣ Total extras
        $extrasServiciosTotal = 0;

        if(!empty($extrasReserva)){
            $extrasServiciosTotal = (float)$extrasReserva->sum('total');
        }

        $opcionesRentaTotal = round($extrasServiciosTotal,2);

        // 8️⃣ Determinar tipo pago
        $tipo = $reservacion->metodo_pago === 'en_linea'
            ? 'en_linea'
            : 'mostrador';

        // 9️⃣ Enviar correo
        if(!empty($reservacion->email_cliente)){

            Mail::to($reservacion->email_cliente)
                ->cc(env('MAIL_FROM_ADDRESS','reservaciones@viajerocar-rental.com'))
                ->send(new ReservacionUsuarioMail(
                    $reservacion,
                    $tipo,
                    $categoria,
                    $extrasReserva,
                    $lugarRetiro,
                    $lugarEntrega,
                    $imgCategoria,
                    $opcionesRentaTotal,
                    $tuAuto
                ));

            return true;
        }

        return false;
    }

    /* =========================================================
       REENVIAR CORREO (web) – llama a la lógica pura y redirige
    ========================================================= */
    public function reenviarCorreo($id)
    {
        try {
            $this->enviarCorreoReservacion($id);
            return back()->with('success','Se ha reenviado el correo de la reservación actualizada.');
        } catch (\Throwable $e) {
            return back()->with('error','Error al reenviar el correo.');
        }
    }
}
