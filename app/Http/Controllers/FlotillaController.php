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
                'c.nombre as categoria'
            )
            ->orderBy('v.modelo', 'asc')
            ->get();

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

        // ✅ Mensajes más claros (sobre todo para iPad / archivos pesados)
        $messages = [
            'id_categoria.required' => 'Selecciona una categoría.',
            'id_categoria.integer'  => 'La categoría no es válida.',
            'archivo_poliza.max' => 'La póliza está muy pesada. Sube un archivo de máximo 10MB.',
            'archivo_verificacion.max' => 'La verificación está muy pesada. Sube un archivo de máximo 10MB.',
            'archivo_poliza.mimes' => 'La póliza debe ser PDF, JPG, JPEG o PNG.',
            'archivo_verificacion.mimes' => 'La verificación debe ser PDF, JPG, JPEG o PNG.',
        ];

        $validated = $request->validate([
            // ✅ categoría
            'id_categoria' => 'required|integer',

            // básicos
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'anio' => "required|integer|min:2000|max:$nextYear",
            'color' => 'nullable|string|max:40',
            'kilometraje' => 'nullable|integer|min:0|max:1000000',
            'numero_rin' => 'nullable|string|max:100',
            'capacidad_tanque' => 'nullable|numeric|min:0',

            // ✅ Archivos: máximo 10MB (10240 KB)
            // Nota: "file" ayuda a que Laravel trate el input como archivo sí o sí
            'archivo_poliza' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'archivo_verificacion' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], $messages);

        // === Subida de archivos (storage/app/public/...) ===
        $archivoPoliza = null;
        if ($request->hasFile('archivo_poliza')) {
            $archivoPoliza = $request->file('archivo_poliza')->store('polizas', 'public');
        }

        $archivoVerificacion = null;
        if ($request->hasFile('archivo_verificacion')) {
            $archivoVerificacion = $request->file('archivo_verificacion')->store('verificaciones', 'public');
        }

        DB::table('vehiculos')->insert([
            // 🔹 Identificadores
            'id_ciudad' => 1,
            'id_sucursal' => 1,
            'id_categoria' => $request->id_categoria,
            'id_estatus' => 1,

            // 🔹 Datos generales
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'anio' => $request->anio,
            'nombre_publico' => $request->nombre_publico ?? "{$request->marca} {$request->modelo} {$request->anio}",
            'color' => $request->color ?? 'Blanco',
            'transmision' => $request->transmision ?? 'Automática',
            'combustible' => $request->combustible ?? 'Gasolina',
            'numero_serie' => $request->numero_serie,
            'numero_rin' => $request->numero_rin,
            'capacidad_tanque' => $request->capacidad_tanque,
            'placa' => $request->placa,

            // 🔹 Datos técnicos
            'cilindros' => $request->cilindros ?? 4,
            'numero_motor' => $request->numero_motor,
            'holograma' => $request->holograma,
            'vigencia_verificacion' => $request->vigencia_verificacion,
            'no_centro_verificacion' => $request->no_centro_verificacion,
            'tipo_verificacion' => $request->tipo_verificacion,
            'kilometraje' => $request->kilometraje ?? 0,
            'asientos' => $request->asientos ?? 5,
            'puertas' => $request->puertas ?? 4,

            // 🔹 Propietario
            'propietario' => $request->propietario ?? 'Viajero Car Rental',
            'rfc_propietario' => $request->rfc_propietario ?? 'VCR010101MX0',
            'domicilio' => $request->domicilio,
            'municipio' => $request->municipio,
            'estado' => $request->estado,
            'pais' => $request->pais ?? 'México',

            // 🔹 Póliza de seguro
            'no_poliza' => $request->no_poliza,
            'aseguradora' => $request->aseguradora,
            'inicio_vigencia_poliza' => $request->inicio_vigencia_poliza,
            'fin_vigencia_poliza' => $request->fin_vigencia_poliza,
            'tipo_cobertura' => $request->tipo_cobertura,
            'plan_seguro' => $request->plan_seguro,
            'archivo_poliza' => $archivoPoliza,

            // 🔹 Tarjeta de circulación / verificación
            'folio_tarjeta' => $request->folio_tarjeta,
            'movimiento_tarjeta' => $request->movimiento_tarjeta,
            'fecha_expedicion_tarjeta' => $request->fecha_expedicion_tarjeta,
            'oficina_expedidora' => $request->oficina_expedidora,
            'archivo_verificacion' => $archivoVerificacion,

            // 🔹 Auditoría
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('rutaFlotilla')
            ->with('success', '🚗 Vehículo agregado correctamente con todos los datos.');
    }

    // 🔹 Editar auto existente
    public function update(Request $request, $id)
    {
        $messages = [
            'archivo_poliza.max' => 'La póliza está muy pesada. Sube un archivo de máximo 10MB.',
            'archivo_verificacion.max' => 'La verificación está muy pesada. Sube un archivo de máximo 10MB.',
            'archivo_poliza.mimes' => 'La póliza debe ser PDF, JPG, JPEG o PNG.',
            'archivo_verificacion.mimes' => 'La verificación debe ser PDF, JPG, JPEG o PNG.',
        ];

        $request->validate([
            'color' => 'nullable|string|max:40',
            'kilometraje' => 'nullable|integer|min:0|max:1000000',
            'id_categoria' => 'nullable|integer',

            // opcional: permitir reemplazar archivos
            'archivo_poliza' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'archivo_verificacion' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], $messages);

        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        $data = [
            'color' => $request->color,
            'kilometraje' => $request->kilometraje,
            'updated_at' => now(),
        ];

        // ✅ si cambian categoría desde modal
        if ($request->filled('id_categoria')) {
            $data['id_categoria'] = $request->id_categoria;
        }

        // ✅ reemplazar póliza si viene
        if ($request->hasFile('archivo_poliza')) {
            if ($vehiculo && $vehiculo->archivo_poliza) {
                Storage::disk('public')->delete($vehiculo->archivo_poliza);
            }
            $data['archivo_poliza'] = $request->file('archivo_poliza')->store('polizas', 'public');
        }

        // ✅ reemplazar verificación si viene
        if ($request->hasFile('archivo_verificacion')) {
            if ($vehiculo && $vehiculo->archivo_verificacion) {
                Storage::disk('public')->delete($vehiculo->archivo_verificacion);
            }
            $data['archivo_verificacion'] = $request->file('archivo_verificacion')->store('verificaciones', 'public');
        }

        DB::table('vehiculos')->where('id_vehiculo', $id)->update($data);

        return redirect()->route('rutaFlotilla')->with('success', 'Vehículo actualizado correctamente.');
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
