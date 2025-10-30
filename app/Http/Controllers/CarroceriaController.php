<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarroceriaController extends Controller
{
    // ==========================================================
    // 📋 Mostrar vista con datos de carrocería
    // ==========================================================
    public function indexView()
    {
        $carrocerias = DB::table('carrocerias as c')
            ->join('vehiculos as v', 'c.id_vehiculo', '=', 'v.id_vehiculo')
            ->select(
                'c.id_carroceria',
                'c.folio',
                'c.fecha',
                'c.zona_afectada',
                'c.tipo_danio',
                'c.severidad',
                'c.taller',
                'c.costo_estimado',
                'c.estatus',
                'v.marca',
                'v.modelo',
                'v.placa',
                'v.anio'
            )
            ->orderByDesc('c.id_carroceria')
            ->get();

        $vehiculos = DB::table('vehiculos')
            ->select('id_vehiculo', 'marca', 'modelo', 'anio', 'placa')
            ->get();

        return view('Admin.carroceria', compact('carrocerias', 'vehiculos'));
    }

    // ==========================================================
    // 💾 Guardar nuevo registro
    // ==========================================================
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_vehiculo'     => 'required|integer|exists:vehiculos,id_vehiculo',
                'zona_afectada'   => 'required|string|max:100',
                'tipo_danio'      => 'required|string|max:100',
                'severidad'       => 'required|string|max:50',
                'taller'          => 'nullable|string|max:120',
                'costo_estimado'  => 'nullable|numeric|min:0',
                'estatus'         => 'required|string|max:50',
            ]);

            // ✅ Generar folio automático
            $ultimo = DB::table('carrocerias')->max('id_carroceria') + 1;
            $folio = 'CAR-' . str_pad($ultimo, 3, '0', STR_PAD_LEFT);

            // ✅ Insertar registro
            $idCarroceria = DB::table('carrocerias')->insertGetId([
                'id_vehiculo'     => $validated['id_vehiculo'],
                'folio'           => $folio,
                'fecha'           => now()->toDateString(),
                'zona_afectada'   => $validated['zona_afectada'],
                'tipo_danio'      => $validated['tipo_danio'],
                'severidad'       => $validated['severidad'],
                'taller'          => $validated['taller'],
                'costo_estimado'  => $validated['costo_estimado'] ?? 0,
                'estatus'         => $validated['estatus'], // 👈 columna correcta confirmada
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // ✅ Registrar gasto si aplica
            if (!empty($validated['costo_estimado']) && $validated['costo_estimado'] > 0) {
                DB::table('gastos')->insert([
                    'id_vehiculo'   => $validated['id_vehiculo'],
                    'id_carroceria' => $idCarroceria,
                    'tipo'          => 'carroceria',
                    'descripcion'   => 'Reparación carrocería (' . $validated['zona_afectada'] . ')',
                    'monto'         => $validated['costo_estimado'],
                    'fecha'         => now()->toDateString(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            return redirect()->route('carroceria.index')->with('success', 'Reporte de carrocería guardado correctamente.');
        } catch (\Exception $e) {
            \Log::error('❌ Error en CarroceriaController@store: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar el reporte. Verifica los datos.');
        }
    }

    // ==========================================================
    // ✏️ Actualizar reporte existente
    // ==========================================================
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'zona_afectada'  => 'required|string|max:100',
                'tipo_danio'     => 'required|string|max:100',
                'severidad'      => 'required|string|max:50',
                'taller'         => 'nullable|string|max:120',
                'costo_estimado' => 'nullable|numeric|min:0',
                'estatus'        => 'required|string|max:50',
            ]);

            DB::table('carrocerias')->where('id_carroceria', $id)->update([
                'zona_afectada'  => $validated['zona_afectada'],
                'tipo_danio'     => $validated['tipo_danio'],
                'severidad'      => $validated['severidad'],
                'taller'         => $validated['taller'],
                'costo_estimado' => $validated['costo_estimado'],
                'estatus'        => $validated['estatus'],
                'updated_at'     => now(),
            ]);

            return redirect()->route('carroceria.index')->with('success', 'Reporte actualizado correctamente.');
        } catch (\Exception $e) {
            \Log::error('❌ Error en CarroceriaController@update: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el reporte.');
        }
    }
}
