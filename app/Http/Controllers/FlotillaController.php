<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FlotillaController extends Controller
{
    // 🔹 Mostrar todos los autos
    public function indexView()
    {
        $vehiculos = DB::table('vehiculos as v')
            ->leftJoin('estatus_carro as e', 'v.id_estatus', '=', 'e.id_estatus')
            ->select(
                'v.id_vehiculo',
                'v.modelo',
                'v.marca',
                'v.anio',
                'v.color',
                'v.placa',
                'v.numero_serie',
                'v.kilometraje',
                'v.categoria',
                'e.nombre as estatus'
            )
            ->orderBy('v.modelo', 'asc')
            ->get();

        return view('Admin.flotilla', compact('vehiculos'));
    }

    // 🔹 Agregar nuevo auto
public function store(Request $request)
{
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;

    $request->validate([
        'marca' => 'required|string|max:100',
        'modelo' => 'required|string|max:100',
        'anio' => "required|integer|min:2000|max:$nextYear",
        'color' => 'nullable|string|max:40',
        'transmision' => 'nullable|string|max:50',
        'combustible' => 'nullable|string|max:50',
        'categoria' => 'nullable|string|max:100',
        'numero_serie' => 'nullable|string|max:100|unique:vehiculos,numero_serie',
        'placa' => 'nullable|string|max:50|unique:vehiculos,placa',
        'kilometraje' => 'nullable|integer|min:0|max:1000000',
        'precio_dia' => 'nullable|numeric|min:0',
        'deposito_garantia' => 'nullable|numeric|min:0',
    ]);

    $nombrePublico = trim("{$request->marca} {$request->modelo} {$request->anio}");

    DB::table('vehiculos')->insert([
        'id_ciudad' => 1,
        'id_sucursal' => 1,
        'id_categoria' => 1,
        'id_estatus' => 1,
        'marca' => $request->marca,
        'modelo' => $request->modelo,
        'anio' => $request->anio,
        'nombre_publico' => $nombrePublico,
        'transmision' => $request->transmision ?? 'Automática',
        'combustible' => $request->combustible ?? 'Gasolina',
        'color' => $request->color ?? 'Blanco',
        'asientos' => $request->asientos ?? 5,
        'puertas' => $request->puertas ?? 4,
        'kilometraje' => $request->kilometraje ?? 0,
        'precio_dia' => $request->precio_dia ?? 0,
        'deposito_garantia' => $request->deposito_garantia ?? 0,
        'placa' => $request->placa,
        'vin' => $request->vin,
        'numero_serie' => $request->numero_serie,
        'categoria' => $request->categoria ?? 'Compacto',
        'descripcion' => $request->descripcion ?? null,

        // datos administrativos por defecto
        'tipo_servicio' => $request->tipo_servicio ?? 'Particular',
        'propietario' => $request->propietario ?? 'Viajero Car Rental',
        'rfc_propietario' => $request->rfc_propietario ?? 'VCR010101MX0',
        'pais' => 'México',
        'inicio_vigencia_poliza' => $request->inicio_vigencia_poliza ?? now(),
        'fin_vigencia_poliza' => $request->fin_vigencia_poliza ?? now()->addYear(),
        'aseguradora' => $request->aseguradora ?? 'Quálitas',
        'tipo_cobertura' => $request->tipo_cobertura ?? 'Amplia',

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('rutaFlotilla')->with('success', '🚗 Vehículo agregado correctamente.');
}



    // 🔹 Editar auto existente
    public function update(Request $request, $id)
    {
        DB::table('vehiculos')->where('id_vehiculo', $id)->update([
            'color' => $request->color,
            'categoria' => $request->categoria,
            'kilometraje' => $request->kilometraje,
            'updated_at' => now(),
        ]);

        return redirect()->route('rutaFlotilla')->with('success', 'Vehículo actualizado correctamente.');
    }

    // 🔹 Eliminar auto
    public function destroy($id)
    {
        DB::table('vehiculos')->where('id_vehiculo', $id)->delete();
        return redirect()->route('rutaFlotilla')->with('success', 'Vehículo eliminado correctamente.');
    }
}
