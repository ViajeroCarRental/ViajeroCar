<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MantenimientoController extends Controller
{
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
                'm.observaciones'
            )
            ->orderBy('v.marca')
            ->get();

        foreach ($vehiculos as $v) {
            $last = $v->ultimo_km_servicio ?? 0;
            $interval = $v->intervalo_km ?? 10000;
            $diff = ($v->kilometraje ?? 0) - $last;

            if ($diff >= $interval) {
                $v->estado_mantenimiento = 'rojo';
            } elseif ($diff >= $interval * 0.8) {
                $v->estado_mantenimiento = 'amarillo';
            } else {
                $v->estado_mantenimiento = 'verde';
            }

            $v->proximo_servicio = $last ? $last + $interval : $interval;
            $v->km_para_proximo = max(0, ($v->proximo_servicio ?? $interval) - ($v->kilometraje ?? 0));
        }

        return view('Admin.mantenimiento', compact('vehiculos'));
    }

    // UPDATE por formulario normal (no AJAX) - opcional
    public function updateKm(Request $request, $id)
    {
        $validated = $request->validate([
            'kilometraje' => 'required|integer|min:0',
            'costo_servicio' => 'nullable|numeric|min:0',
        ]);

        DB::table('vehiculos')->where('id_vehiculo', $id)->update([
            'kilometraje' => $validated['kilometraje'],
            'updated_at' => now(),
        ]);

        $m = DB::table('mantenimientos')->where('id_vehiculo', $id)->first();

        $intervalo = $m->intervalo_km ?? 10000;
        $ultimo = $m->ultimo_km_servicio ?? 0;
        $diff = $validated['kilometraje'] - $ultimo;

        $estado = 'verde';
        if ($diff >= $intervalo) $estado = 'rojo';
        elseif ($diff >= $intervalo * 0.8) $estado = 'amarillo';

        if ($m) {
            DB::table('mantenimientos')->where('id_vehiculo', $id)->update([
                'kilometraje_actual' => $validated['kilometraje'],
                'costo_servicio' => $validated['costo_servicio'] ?? $m->costo_servicio,
                'estatus' => $estado,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('mantenimientos')->insert([
                'id_vehiculo' => $id,
                'kilometraje_actual' => $validated['kilometraje'],
                'ultimo_km_servicio' => 0,
                'intervalo_km' => 10000,
                'costo_servicio' => $validated['costo_servicio'] ?? 0,
                'estatus' => $estado,
                'created_at' => now(),
            ]);
        }

        return redirect()->route('rutaMantenimiento')->with('success', 'Datos actualizados correctamente.');
    }

    // Endpoint que recibe AJAX y devuelve JSON con nuevo estado
    public function registrarMantenimiento(Request $request, $id)
    {
            $rules = [
            'kilometraje_servicio' => 'required|integer|min:0',
            'costo_servicio' => 'nullable|numeric|min:0',
            // ✅ ahora permitimos "on" o "0" sin error
            'cambio_aceite' => 'nullable|in:0,1,on,off,true,false',
            'tipo_aceite' => 'nullable|string|max:100',
            'rotacion_llantas' => 'nullable|in:0,1,on,off,true,false',
            'cambio_filtro' => 'nullable|in:0,1,on,off,true,false',
            'cambio_pastillas' => 'nullable|in:0,1,on,off,true,false',
            'observaciones' => 'nullable|string|max:2000',
        ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // Si fue AJAX devolvemos errores en JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'ultimo_km_servicio' => $request->input('kilometraje_servicio'),
            'kilometraje_actual' => $request->input('kilometraje_servicio'),
            'fecha_servicio' => now(),
            'costo_servicio' => $request->input('costo_servicio', 0),
            'cambio_aceite' => $request->boolean('cambio_aceite'),
            'tipo_aceite' => $request->input('tipo_aceite'),
            'rotacion_llantas' => $request->boolean('rotacion_llantas'),
            'cambio_filtro' => $request->boolean('cambio_filtro'),
            'cambio_pastillas' => $request->boolean('cambio_pastillas'),
            'observaciones' => $request->input('observaciones'),
            'updated_at' => now(),
            'estatus' => 'verde',
        ];

        DB::table('mantenimientos')->updateOrInsert(
            ['id_vehiculo' => $id],
            $data
        );

        // Actualizar kilometraje del vehículo
        DB::table('vehiculos')->where('id_vehiculo', $id)->update([
            'kilometraje' => $request->input('kilometraje_servicio'),
            'updated_at' => now(),
        ]);

        // Guardar gasto
        DB::table('gastos')->insert([
            'id_vehiculo' => $id,
            'tipo' => 'mantenimiento',
            'descripcion' => 'Mantenimiento: ' . implode(', ', array_filter([
                $request->boolean('cambio_aceite') ? 'Cambio aceite' : null,
                $request->boolean('rotacion_llantas') ? 'Rotación llantas' : null,
                $request->boolean('cambio_filtro') ? 'Cambio filtro' : null,
                $request->boolean('cambio_pastillas') ? 'Cambio pastillas' : null,
            ])),
            'monto' => $request->input('costo_servicio', 0),
            'fecha' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Recalcular estado y valores para devolver al frontend
        $m = DB::table('mantenimientos')->where('id_vehiculo', $id)->first();
        $v = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        $last = $m->ultimo_km_servicio ?? 0;
        $interval = $m->intervalo_km ?? 10000;
        $diff = ($v->kilometraje ?? 0) - $last;

        $estado = 'verde';
        if ($diff >= $interval) $estado = 'rojo';
        elseif ($diff >= $interval * 0.8) $estado = 'amarillo';

        $proximo = $last ? $last + $interval : $interval;
        $km_para_proximo = max(0, $proximo - ($v->kilometraje ?? 0));

        // Respuesta JSON con nuevos datos
        return response()->json([
            'success' => true,
            'id_vehiculo' => $id,
            'kilometraje' => (int)$v->kilometraje,
            'ultimo_km_servicio' => (int)$last,
            'proximo_servicio' => (int)$proximo,
            'km_para_proximo' => $km_para_proximo,
            'estado' => $estado,
            'mensaje' => 'Mantenimiento registrado correctamente.'
        ]);
    }
}
