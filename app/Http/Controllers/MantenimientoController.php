<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MantenimientoController extends Controller
{
    /**
     * Mostrar vista principal del mantenimiento
     */
    public function indexView()
    {
        $vehiculos = DB::table('vehiculos as v')
            ->leftJoin('mantenimientos as m', 'v.id_vehiculo', '=', 'm.id_vehiculo')
            ->leftJoin('estatus_carro as e', 'v.id_estatus', '=', 'e.id_estatus')
            ->select(
                'v.id_vehiculo',
                'v.marca',
                'v.modelo',
                'v.anio',
                'v.placa',
                'v.color',
                'v.kilometraje',
                'v.aceite',
                'e.nombre as estatus',
                'm.ultimo_km_servicio',
                'm.intervalo_km',
                'm.costo_servicio',
                'm.fecha_servicio',
                'm.cambio_aceite',
                'm.tipo_aceite',
                'm.rotacion_llantas',
                'm.cambio_filtro',
                'm.cambio_pastillas',
                'm.observaciones',

                'm.tipo_mantenimiento',   // 👈 AÑADE ESTA LÍNEA
                'm.otro',
                'm.rellenar_aceite',
                'm.nivel_agua',
                'm.presion_llantas',
                'm.limpieza_general'
            )
            ->orderBy('v.marca')
            ->get();

        foreach ($vehiculos as $v) {
            $ultimo = (int)($v->ultimo_km_servicio ?? 0);
            $intervalo = (int)($v->intervalo_km ?? 10000);
            $actual = (int)($v->kilometraje ?? 0);

            // 🔹 Si no hay registro previo, asumimos múltiplo anterior al actual
            if ($ultimo == 0 || $actual < $ultimo) {
                $ultimo = floor($actual / $intervalo) * $intervalo;
            }

            // 🔹 Próximo servicio y cuánto falta
            $proximo = $ultimo + $intervalo;
            $falta = max(0, $proximo - $actual);

            // 🔸 Color según distancia
            if ($falta <= 0) {
                $color = 'rojo';
            } elseif ($falta <= ($intervalo * 0.2)) {
                $color = 'amarillo';
            } else {
                $color = 'verde';
            }

            $v->estado_mantenimiento = $color;
            $v->proximo_servicio = $proximo;
            $v->km_para_proximo = $falta;
            $v->ultimo_km_servicio = $ultimo;
        }

        return view('Admin.mantenimiento', compact('vehiculos'));
    }

    /**
     * Actualiza el kilometraje del vehículo manualmente
     */
    public function updateKm(Request $request, $id)
    {
        $validated = $request->validate([
            'kilometraje'     => 'required|integer|min:0',
            'costo_servicio'  => 'nullable|numeric|min:0',
            'intervalo_km'    => 'nullable|integer|min:1000|max:50000',
        ]);

        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();
        $m = DB::table('mantenimientos')->where('id_vehiculo', $id)->first();

        $intervalo = $validated['intervalo_km'] ?? ($m->intervalo_km ?? 10000);
        $actual = $validated['kilometraje'];
        $ultimo = $m->ultimo_km_servicio ?? 0;

        // 🔹 Si no existe último servicio, calcular múltiplo anterior al actual
        if ($ultimo == 0 || $actual < $ultimo) {
            $ultimo = floor($actual / $intervalo) * $intervalo;
        }

        $proximo = $ultimo + $intervalo;
        $falta = max(0, $proximo - $actual);

        if ($falta <= 0) $estado = 'rojo';
        elseif ($falta <= ($intervalo * 0.2)) $estado = 'amarillo';
        else $estado = 'verde';

        // 🔹 Actualizar vehículo
        DB::table('vehiculos')->where('id_vehiculo', $id)->update([
            'kilometraje' => $actual,
            'updated_at'  => now(),
        ]);

        // 🔹 Actualizar o insertar mantenimiento
        DB::table('mantenimientos')->updateOrInsert(
            ['id_vehiculo' => $id],
            [
                'kilometraje_actual' => $actual,
                'ultimo_km_servicio' => $ultimo,
                'intervalo_km'       => $intervalo,
                'proximo_servicio'   => $proximo,
                'costo_servicio'     => $validated['costo_servicio'] ?? 0,
                'estatus'            => $estado,
                'updated_at'         => now(),
            ]
        );

        return redirect()->route('rutaMantenimiento')->with('success', 'Kilometraje actualizado correctamente.');
    }

    /**
     * Registrar un mantenimiento completo
     */
    public function registrarMantenimiento(Request $request, $id)
    {
        $rules = [
            'kilometraje_servicio' => 'required|integer|min:0',
            'intervalo_km'         => 'nullable|integer|min:1000|max:50000',
            'costo_servicio'       => 'nullable|numeric|min:0',
            'cambio_aceite'        => 'nullable|in:0,1,on,off,true,false',
            'tipo_aceite'          => 'nullable|string|max:100',
            'rotacion_llantas'     => 'nullable|in:0,1,on,off,true,false',
            'cambio_filtro'        => 'nullable|in:0,1,on,off,true,false',
            'cambio_pastillas'     => 'nullable|in:0,1,on,off,true,false',
            'rellenar_aceite'      => 'nullable|in:0,1,on,off,true,false',
            'nivel_agua'           => 'nullable|in:0,1,on,off,true,false',
            'presion_llantas'      => 'nullable|in:0,1,on,off,true,false',
            'limpieza_general'     => 'nullable|in:0,1,on,off,true,false',
            'tipo_mantenimiento'   => 'nullable|in:menor,mayor',
            'otro'                 => 'nullable|string|max:255',
            'observaciones'        => 'nullable|string|max:2000',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : redirect()->back()->withErrors($validator)->withInput();
        }

        $intervalo = (int)($request->input('intervalo_km', 10000));
        $km_servicio = (int)$request->input('kilometraje_servicio');

        // 🔹 Registrar mantenimiento
        $ultimo = $km_servicio;
        $proximo = $ultimo + $intervalo;
        $falta = max(0, $proximo - $km_servicio);

        if ($falta <= 0) $estado = 'rojo';
        elseif ($falta <= ($intervalo * 0.2)) $estado = 'amarillo';
        else $estado = 'verde';

        $data = [
            'ultimo_km_servicio' => $ultimo,
            'kilometraje_actual' => $km_servicio,
            'intervalo_km'       => $intervalo,
            'proximo_servicio'   => $proximo,
            'fecha_servicio'     => now(),
            'costo_servicio'     => $request->input('costo_servicio', 0),
            'cambio_aceite'      => $request->boolean('cambio_aceite'),
            'tipo_aceite'        => $request->input('tipo_aceite'),
            'rotacion_llantas'   => $request->boolean('rotacion_llantas'),
            'cambio_filtro'      => $request->boolean('cambio_filtro'),
            'cambio_pastillas'   => $request->boolean('cambio_pastillas'),
            'observaciones'      => $request->input('observaciones'),
            'estatus'            => $estado,
            'updated_at'         => now(),
            'tipo_mantenimiento' => $request->input('tipo_mantenimiento'),
            'otro'               => $request->input('otro'),
            'rellenar_aceite' => $request->boolean('rellenar_aceite'),
            'nivel_agua' => $request->boolean('nivel_agua'),
            'presion_llantas' => $request->boolean('presion_llantas'),
            'limpieza_general' => $request->boolean('limpieza_general'),

        ];

        DB::table('mantenimientos')->updateOrInsert(['id_vehiculo' => $id], $data);

        // 🔹 Actualizar vehículo
        DB::table('vehiculos')->where('id_vehiculo', $id)->update([
            'kilometraje' => $km_servicio,
            'updated_at'  => now(),
        ]);

        // 🔹 Registrar gasto
        DB::table('gastos')->insert([
            'id_vehiculo'  => $id,
            'tipo'         => 'mantenimiento',
            'descripcion'  => 'Mantenimiento realizado (km ' . $km_servicio . ')',
            'monto'        => $request->input('costo_servicio', 0),
            'fecha'        => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json([
            'success'            => true,
            'id_vehiculo'        => $id,
            'kilometraje'        => $km_servicio,
            'ultimo_km_servicio' => $ultimo,
            'proximo_servicio'   => $proximo,
            'falta'              => $falta,
            'estado'             => $estado,
            'mensaje'            => 'Mantenimiento registrado correctamente.',
        ]);
    }
}
