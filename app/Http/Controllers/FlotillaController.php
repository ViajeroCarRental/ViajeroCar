<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FlotillaController extends Controller
{
    // 🔹 Mostrar todos los autos
public function indexView()
{
    $vehiculos = DB::table('vehiculos as v')
        ->leftJoin('estatus_carro as e', 'v.id_estatus', '=', 'e.id_estatus')
        ->leftJoin('categorias_carros as c', 'v.id_categoria', '=', 'c.id_categoria')
        ->select(
            'v.id_vehiculo',
            'v.modelo',
            'v.marca',
            'v.anio',
            'v.color',
            'v.placa',
            'v.numero_serie',
            'v.numero_rin',
            'v.kilometraje',
            'v.capacidad_tanque',
            'e.nombre as estatus',
            'c.nombre as categoria' // ✅ nombre de la categoría desde la tabla
        )
        ->orderBy('v.modelo', 'asc')
        ->get();

    // 🔹 Ahora sí definimos $categorias para el modal
    $categorias = DB::table('categorias_carros')
        ->where('activo', true)
        ->orderBy('nombre')
        ->get();

    return view('Admin.flotilla', compact('vehiculos', 'categorias'));
}



    // 🔹 Agregar nuevo auto
public function store(Request $request)
{
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;

    $validated = $request->validate([
        'marca' => 'required|string|max:100',
        'modelo' => 'required|string|max:100',
        'anio' => "required|integer|min:2000|max:$nextYear",
        'color' => 'nullable|string|max:40',
        'kilometraje' => 'nullable|integer|min:0|max:1000000',
        'archivo_poliza' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'archivo_verificacion' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'archivo_cartafactura' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'archivo_tarjetacirculacion' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'numero_rin' => 'nullable|string|max:100',
        'capacidad_tanque' => 'nullable|numeric|min:0',
        'aceite' => 'nullable|string|max:100',
        // (si quieres, aquí podrías agregar más validaciones de otros campos)
    ]);

    // === Subida de archivos ===
    $archivopoliza = $request->hasFile('archivo_poliza')
    ? file_get_contents($request->file('archivo_poliza')->getRealPath())
    : null;

    $archivoverificacion = $request->hasFile('archivo_verificacion')
    ? file_get_contents($request->file('archivo_verificacion')->getRealPath())
    : null;

    $archivocartafactura = $request->hasFile('archivo_cartafactura')
    ? file_get_contents($request->file('archivo_cartafactura')->getRealPath())
    : null;

    $archivotarjetacirculacion = $request->hasFile('archivo_tarjetacirculacion')
    ? file_get_contents($request->file('archivo_tarjetacirculacion')->getRealPath())
    : null;


    // === Inserción completa ===
    DB::table('vehiculos')->insert([
        // 🔹 Identificadores
        'id_ciudad'   => 1,
        'id_sucursal' => 1,
        'id_categoria'=> $request->id_categoria, // ✅ categoría vinculada
        'id_estatus'  => 1,

        // 🔹 Datos generales
        'marca'          => $request->marca,
        'modelo'         => $request->modelo,
        'anio'           => $request->anio,
        'nombre_publico' => $request->nombre_publico ?? "{$request->marca} {$request->modelo} {$request->anio}",
        'color'          => $request->color ?? 'Blanco',
        'transmision'    => $request->transmision ?? 'Automática',
        'combustible'    => $request->combustible ?? 'Gasolina',
        'numero_serie'   => $request->numero_serie,
        'numero_rin'     => $request->numero_rin,
        'capacidad_tanque'=> $request->capacidad_tanque,
        'aceite'         => $request->aceite,
        'placa'          => $request->placa,
        'archivo_cartafactura' => $archivocartafactura,

        // 🔹 Datos técnicos
        'cilindros'             => $request->cilindros ?? 4,
        'numero_motor'          => $request->numero_motor,
        'holograma'             => $request->holograma,
        'vigencia_verificacion' => $request->vigencia_verificacion,
        'no_centro_verificacion'=> $request->no_centro_verificacion,
        'tipo_verificacion'     => $request->tipo_verificacion,
        'kilometraje'           => $request->kilometraje ?? 0,
        'asientos'              => $request->asientos ?? 5,
        'puertas'               => $request->puertas ?? 4,

        // 🔹 Propietario
        'propietario'     => $request->propietario ?? 'Viajero Car Rental',
        'rfc_propietario' => $request->rfc_propietario ?? 'VCR010101MX0',
        'domicilio'       => $request->domicilio,
        'municipio'       => $request->municipio,
        'estado'          => $request->estado,
        'pais'            => $request->pais ?? 'México',

        // 🔹 Póliza de seguro
        'no_poliza'             => $request->no_poliza,
        'aseguradora'           => $request->aseguradora,
        'inicio_vigencia_poliza'=> $request->inicio_vigencia_poliza,
        'fin_vigencia_poliza'   => $request->fin_vigencia_poliza,
        'tipo_cobertura'        => $request->tipo_cobertura,
        'plan_seguro'           => $request->plan_seguro,
        'archivo_poliza'        => $archivopoliza,

        // 🔹 Tarjeta de circulación / verificación
        'folio_tarjeta'           => $request->folio_tarjeta,
        'movimiento_tarjeta'      => $request->movimiento_tarjeta,
        'fecha_expedicion_tarjeta'=> $request->fecha_expedicion_tarjeta,
        'oficina_expedidora'      => $request->oficina_expedidora,
        'archivo_verificacion'    => $archivoverificacion,
        'archivo_tarjetacirculacion' => $archivotarjetacirculacion,

        // 🔹 Fechas de auditoría
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // ⬇⬇⬇ AQUÍ VIENE LO IMPORTANTE PARA EL iPAD / FETCH ⬇⬇⬇
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => '🚗 Vehículo agregado correctamente con todos los datos.',
        ]);
    }

    // Petición normal de navegador (sin fetch)
    return redirect()
        ->route('rutaFlotilla')
        ->with('success', '🚗 Vehículo agregado correctamente con todos los datos.');
}


















public function getVehiculo($id)
{
    $vehiculo = DB::table('vehiculos')
        ->where('id_vehiculo', $id)
        ->first();

    if (!$vehiculo) {
        return response()->json(['error' => 'Vehículo no encontrado'], 404);
    }
    // ❌ ELIMINAR CAMPOS BINARIOS
    unset(
        $vehiculo->archivo_cartafactura,
        $vehiculo->archivo_poliza,
        $vehiculo->archivo_verificacion,
        $vehiculo->archivo_tarjetacirculacion
    );

    // 🔒 Normalizar null
    foreach ($vehiculo as $k => $v) {
        if ($v === null) {
            $vehiculo->$k = '';
        }
    }

    return response()->json($vehiculo);
}



    // 🔹 Editar auto existente
    public function update(Request $request, $id)
{
     $currentYear = date('Y');
    $nextYear = $currentYear + 1;

    $validated = $request->validate([
        'marca' => 'sometimes|required|string|max:100',
        'modelo' => 'sometimes|required|string|max:100',
        'anio' => "sometimes|required|integer|min:2000|max:$nextYear",
        'color' => 'nullable|string|max:40',
        'kilometraje' => 'nullable|integer|min:0|max:1000000',
        'archivo_poliza' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'archivo_verificacion' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'archivo_cartafactura' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'archivo_tarjetacirculacion' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        'numero_rin' => 'nullable|string|max:100',
        'capacidad_tanque' => 'nullable|numeric|min:0',
        'aceite' => 'nullable|string|max:100',
    ]);

    $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();
    if (!$vehiculo) abort(404);

    $data = [];

$campos = [
    // Generales
    'marca',
    'modelo',
    'anio',
    'nombre_publico',
    'color',
    'transmision',
    'combustible',
    'id_categoria',
    'numero_serie',
    'numero_rin',
    'placa',

    // Técnicos
    'cilindros',
    'numero_motor',
    'holograma',
    'vigencia_verificacion',
    'kilometraje',
    'asientos',
    'puertas',
    'capacidad_tanque',
    'aceite',

    // Propietario
    'propietario',

    // Seguro
    'no_poliza',
    'aseguradora',
    'inicio_vigencia_poliza',
    'fin_vigencia_poliza',

    // Tarjeta
    'folio_tarjeta',
    'movimiento_tarjeta',
    'fecha_expedicion_tarjeta',
];

foreach ($campos as $campo) {
    if ($request->filled($campo)) {
        $data[$campo] = $request->$campo;
    }
}

    // ========= ARCHIVOS (solo si llegan) =========
    if ($request->hasFile('archivo_poliza')) {
        $data['archivo_poliza'] = file_get_contents($request->file('archivo_poliza')->getRealPath());
    }

    if ($request->hasFile('archivo_verificacion')) {
        $data['archivo_verificacion'] = file_get_contents($request->file('archivo_verificacion')->getRealPath());
    }

    if ($request->hasFile('archivo_cartafactura')) {
        $data['archivo_cartafactura'] = file_get_contents($request->file('archivo_cartafactura')->getRealPath());
    }

    if ($request->hasFile('archivo_tarjetacirculacion')) {
        $data['archivo_tarjetacirculacion'] = file_get_contents($request->file('archivo_tarjetacirculacion')->getRealPath());
    }


    if (empty($data)) {
    return back()->with('info', 'No se realizaron cambios.');
    }

    $data['updated_at'] = now();

    DB::table('vehiculos')->where('id_vehiculo', $id)->update($data);

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => '🚗 Vehículo actualizado correctamente.'
        ]);
    }

    return redirect()
        ->route('rutaFlotilla')
        ->with('success', '🚗 Vehículo actualizado correctamente.');
}

    // 🔹 Eliminar auto
    public function destroy($id)
    {
        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        if ($vehiculo) {
            if ($vehiculo->archivo_poliza) Storage::disk('public')->delete($vehiculo->archivo_poliza);
            if ($vehiculo->archivo_verificacion) Storage::disk('public')->delete($vehiculo->archivo_verificacion);
        }

        DB::table('vehiculos')->where('id_vehiculo', $id)->delete();
        return redirect()->route('rutaFlotilla')->with('success', 'Vehículo eliminado correctamente.');
    }
}
