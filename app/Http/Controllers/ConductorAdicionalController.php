<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\AnexoConductorAdicionalMail;

class ConductorAdicionalController extends Controller
{
    /* ============================================================
       📄 MOSTRAR ANEXO (por CONTRATO)
       $id = id_contrato
    ============================================================ */
    public function verAnexo($id) // $id = id_contrato
    {
        // 1) Contrato (aquí vive la firma del arrendador)
        $contrato = DB::table('contratos')
            ->where('id_contrato', $id)
            ->select('id_contrato', 'id_reservacion', 'numero_contrato', 'firma_arrendador')
            ->first();

        if (!$contrato) {
            abort(404, 'Contrato no encontrado para el anexo.');
        }

        // 2) Reservación (titular)
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $contrato->id_reservacion)
            ->select(
                'id_reservacion',
                'nombre_cliente',
                'apellidos_cliente',
                'email_cliente',
                'telefono_cliente'
            )
            ->first();

        // 3) Todos los conductores ligados al contrato
        $conductores = DB::table('contrato_conductor_adicional as cca')
            ->leftJoin('contrato_documento as cd', function ($join) {
                $join->on('cd.id_conductor', '=', 'cca.id_conductor')
                     ->where('cd.tipo', '=', 'licencia');
            })
            ->where('cca.id_contrato', $id)
            ->select(
                'cca.id_conductor',
                'cca.nombres',
                'cca.apellidos',
                'cca.numero_licencia',
                'cca.fecha_nacimiento',
                'cd.fecha_vencimiento as vence',
                'cca.firma_conductor',
                'cca.firmado',
                'cca.firmado_en'
            )
            ->get();

        // 4) Filtrar al titular (mismo nombre + apellidos que la reservación)
        if ($reservacion) {
            // Nombre completo del titular normalizado
            $nombreTitular = trim(mb_strtoupper(
                ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? ''),
                'UTF-8'
            ));

            $conductores = $conductores->filter(function ($c) use ($nombreTitular) {
                $nombreConductor = trim(mb_strtoupper(
                    ($c->nombres ?? '') . ' ' . ($c->apellidos ?? ''),
                    'UTF-8'
                ));

                // Solo dejamos pasar a los que NO son el titular
                return $nombreConductor !== $nombreTitular;
            })->values(); // reindexar la colección
        }

        return view('Admin.anexo-conductores', compact('contrato', 'reservacion', 'conductores'));
    }

    /* ============================================================
       🗑 ELIMINAR CONDUCTOR ADICIONAL DEL CONTRATO
       (borra también sus documentos de contrato_documento)
    ============================================================ */
    public function eliminar($id)
    {
        DB::beginTransaction();

        try {
            // Borrar documentos ligados a ese conductor
            DB::table('contrato_documento')
                ->where('id_conductor', $id)
                ->delete();

            // Borrar conductor adicional
            DB::table('contrato_conductor_adicional')
                ->where('id_conductor', $id)
                ->delete();

            DB::commit();

            return back()->with('ok', 'Conductor adicional eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar conductor adicional: ' . $e->getMessage());

            return back()->with('error', 'No se pudo eliminar el conductor adicional.');
        }
    }

    /* ============================================================
       ✍️ GUARDAR FIRMA DEL ARRENDADOR (desde ANEXO)
       Recibe id_contrato + firma (base64)
    ============================================================ */
    public function guardarFirmaArrendador(Request $request)
    {
        $request->validate([
            'id_contrato' => 'required|integer|exists:contratos,id_contrato',
            'firma'       => 'required|string',
        ]);

        try {
            $carpeta = public_path('firmas');
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            $imagenBase64 = $request->firma;
            $imagenBase64 = str_replace('data:image/png;base64,', '', $imagenBase64);
            $imagenBase64 = str_replace(' ', '+', $imagenBase64);

            $nombreArchivo = 'firma_arrendador_contrato_' . $request->id_contrato . '_' . time() . '.png';
            $rutaFisica    = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;

            file_put_contents($rutaFisica, base64_decode($imagenBase64));

            $rutaPublica = 'firmas/' . $nombreArchivo;

            DB::table('contratos')
                ->where('id_contrato', $request->id_contrato)
                ->update([
                    'firma_arrendador' => $rutaPublica,
                    'updated_at'       => now(),
                ]);

            return response()->json([
                'ok'         => true,
                'ruta_firma' => $rutaPublica,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar firma arrendador (anexo): ' . $e->getMessage());

            return response()->json([
                'ok'    => false,
                'error' => 'No se pudo guardar la firma del arrendador.',
            ], 500);
        }
    }

    /* ============================================================
       ✍️ GUARDAR FIRMA DE CONDUCTOR ADICIONAL
       Espera: id_conductor + firma (base64)
    ============================================================ */
    public function guardarFirmaConductor(Request $request)
    {
        $request->validate([
            'id_conductor' => 'required|integer|exists:contrato_conductor_adicional,id_conductor',
            'firma'        => 'required|string', // base64
        ]);

        try {
            // Carpeta para firmas de conductores
            $carpeta = public_path('firmas_conductores');

            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            // Decodificar base64
            $imagenBase64 = $request->firma;
            $imagenBase64 = str_replace('data:image/png;base64,', '', $imagenBase64);
            $imagenBase64 = str_replace(' ', '+', $imagenBase64);

            $nombreArchivo = 'firma_conductor_' . $request->id_conductor . '_' . time() . '.png';
            $rutaFisica    = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;

            file_put_contents($rutaFisica, base64_decode($imagenBase64));

            // Ruta relativa para asset()
            $rutaPublica = 'firmas_conductores/' . $nombreArchivo;

            // Actualizar conductor adicional
            DB::table('contrato_conductor_adicional')
                ->where('id_conductor', $request->id_conductor)
                ->update([
                    'firma_conductor' => $rutaPublica,
                    'firmado'         => 1,
                    'firmado_en'      => now(),
                    'updated_at'      => now(),
                ]);

            return response()->json([
                'ok'           => true,
                'ruta_firma'   => $rutaPublica,
                'id_conductor' => $request->id_conductor,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar firma de conductor adicional: ' . $e->getMessage());

            return response()->json([
                'ok'    => false,
                'error' => 'No se pudo guardar la firma del conductor.',
            ], 500);
        }
    }

        /* ============================================================
       📧 ENVIAR ANEXOS DE CONDUCTORES ADICIONALES (por CONTRATO)
       Genera un PDF por conductor adicional válido y lo adjunta al correo
    ============================================================ */
    public function enviarAnexos($id)
    {
        try {
            // 1) Buscar contrato
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->select('id_contrato', 'id_reservacion', 'numero_contrato', 'firma_arrendador')
                ->first();

            if (!$contrato) {
                return back()->with('error', 'Contrato no encontrado para enviar anexos.');
            }

            // 2) Reservación (titular)
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $contrato->id_reservacion)
                ->select(
                    'id_reservacion',
                    'nombre_cliente',
                    'apellidos_cliente',
                    'email_cliente',
                    'fecha_inicio',
                    'hora_retiro'
                )
                ->first();

            if (!$reservacion || empty($reservacion->email_cliente)) {
                return back()->with('error', 'La reservación no tiene correo válido para enviar los anexos.');
            }

            // 3) Todos los conductores ligados al contrato (incluye titular y placeholders)
            $conductores = DB::table('contrato_conductor_adicional as cca')
                ->leftJoin('contrato_documento as cd', function ($join) {
                    $join->on('cd.id_conductor', '=', 'cca.id_conductor')
                         ->where('cd.tipo', '=', 'licencia');
                })
                ->where('cca.id_contrato', $id)
                ->select(
                    'cca.id_conductor',
                    'cca.nombres',
                    'cca.apellidos',
                    'cca.numero_licencia',
                    'cca.fecha_nacimiento',
                    'cd.fecha_vencimiento as vence',
                    'cca.firma_conductor',
                    'cca.firmado',
                    'cca.firmado_en'
                )
                ->get();

            if ($conductores->isEmpty()) {
                return back()->with('error', 'No hay conductores adicionales registrados para este contrato.');
            }

            // 4) Nombre completo del titular (normalizado)
            $nombreTitular = trim(mb_strtoupper(
                ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? ''),
                'UTF-8'
            ));

            // 5) Filtrar conductores válidos para generar PDF
            $conductoresValidos = $conductores->filter(function ($c) use ($nombreTitular) {
                // Nombre completo del conductor
                $nombreConductor = trim(mb_strtoupper(
                    ($c->nombres ?? '') . ' ' . ($c->apellidos ?? ''),
                    'UTF-8'
                ));

                // a) Excluir titular (mismo nombre que la reservación)
                if ($nombreConductor !== '' && $nombreConductor === $nombreTitular) {
                    return false;
                }

                // b) Excluir placeholders "Conductor adicional X"
                $nombresBruto = trim(mb_strtoupper($c->nombres ?? '', 'UTF-8'));

                if (
                    strpos($nombresBruto, 'CONDUCTOR ADICIONAL') !== false ||
                    strpos($nombreConductor, 'CONDUCTOR ADICIONAL') !== false
                ) {
                    return false;
                }

                // c) Excluir si NO tiene firma
                if (empty($c->firma_conductor)) {
                    return false;
                }

                return true;
            })->values();

            if ($conductoresValidos->isEmpty()) {
                return back()->with('error', 'No hay conductores adicionales válidos (con nombre real y firma) para generar anexos.');
            }

            // 6) Generar un PDF por cada conductor válido
            $rutasPdfs = [];

            $carpeta = storage_path('app/anexos_conductores');
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            foreach ($conductoresValidos as $conductor) {
                // Renderizar vista del PDF
                $pdf = Pdf::loadView('Admin.anexo-conductor-pdf', [
                    'contrato'    => $contrato,
                    'reservacion' => $reservacion,
                    'conductor'   => $conductor,
                ]);

                $nombreArchivo = 'anexo_conductor_'
                    . $contrato->id_contrato . '_'
                    . $conductor->id_conductor . '.pdf';

                $rutaCompleta = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;

                // Guardar el PDF en disco
                $pdf->save($rutaCompleta);

                $rutasPdfs[] = $rutaCompleta;
            }

            if (empty($rutasPdfs)) {
                return back()->with('error', 'No se pudieron generar los PDFs de los anexos.');
            }

            // 7) Enviar correo con TODOS los PDFs adjuntos
            Mail::to($reservacion->email_cliente)
                ->send(new AnexoConductorAdicionalMail($reservacion, $contrato, $rutasPdfs));

            // 8) (Opcional) Borrar PDFs temporales
            foreach ($rutasPdfs as $ruta) {
                if (file_exists($ruta)) {
                    @unlink($ruta);
                }
            }

            return back()->with('ok', 'Anexos de conductores adicionales enviados correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al enviar anexos de conductores adicionales: ' . $e->getMessage());

            return back()->with('error', 'Ocurrió un error al generar o enviar los anexos de conductores.');
        }
    }

}
