<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Services\FacturapiService;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacturaController extends Controller
{
    // Post factura desde admin
    public function store(Request $request, FacturapiService $facturapi)
    {
        $datos = $request->validate([
            'nombre_razon_social' => 'required|string|max:254',
            'rfc'                 => 'required|string|min:12|max:13',
            'regimen_fiscal'      => 'required|string',
            'codigo_postal'       => 'required|string|size:5',
            'uso_cfdi'            => 'required|string',
            'correo'              => 'required|email',
            'folio_reservacion'   => 'required|string',
            'metodo_pago'         => 'required|in:PUE,PPD',
            'forma_pago'          => 'required|string',
            'clave_producto'      => 'required|string',
            'cantidad'            => 'required|numeric|min:1',
            'unidad_sat'          => 'required|string',
            'descripcion'         => 'required|string',
            'valor_unitario'      => 'required|numeric|min:0',
        ]);

        $datos['rfc'] = strtoupper(trim($datos['rfc']));

        try {
            $respuesta = $facturapi->crearFactura([
                'customer' => [
                    'legal_name' => $datos['nombre_razon_social'],
                    'tax_id'     => $datos['rfc'],
                    'tax_system' => $datos['regimen_fiscal'],
                    'email'      => $datos['correo'],
                    'address'    => ['zip' => $datos['codigo_postal']],
                ],
                'use'            => $datos['uso_cfdi'],
                'payment_method' => $datos['metodo_pago'],
                'payment_form'   => $datos['forma_pago'],
                'items' => [[
                    'quantity' => (float) $datos['cantidad'],
                    'product'  => [
                        'description'  => $datos['descripcion'],
                        'product_key'  => $datos['clave_producto'],
                        'unit_key'     => $datos['unidad_sat'],
                        'price'        => (float) $datos['valor_unitario'],
                        'tax_included' => false,
                        'taxes' => [['type' => 'IVA', 'rate' => 0.16]],
                    ],
                ]],
            ]);

            $factura = Factura::create([
                'facturapi_id'      => $respuesta['id'],
                'uuid'              => $respuesta['uuid'] ?? null,
                'folio_reservacion' => $datos['folio_reservacion'],
                'rfc_receptor'      => $datos['rfc'],
                'nombre_receptor'   => $datos['nombre_razon_social'],
                'total'             => $respuesta['total'] ?? 0,
                'status'            => $respuesta['status'] ?? 'valid',
                'estatus'           => 'timbrada',
                'origen'            => 'admin',
                'fecha_timbrado'    => now(),
            ]);

            // Guardar XML y PDF
            try {
                $contenidoXml = $facturapi->descargarXml($respuesta['id']);
                $contenidoPdf = $facturapi->descargarPdf($respuesta['id']);
                $nombreBase = 'factura_' . $datos['folio_reservacion'] . '_' . $respuesta['id'];
                $rutaXml = 'facturas/' . $nombreBase . '.xml';
                $rutaPdf = 'facturas/' . $nombreBase . '.pdf';
                Storage::put($rutaXml, $contenidoXml);
                Storage::put($rutaPdf, $contenidoPdf);
                $factura->update(['ruta_xml' => $rutaXml, 'ruta_pdf' => $rutaPdf]);
            } catch (\Exception $e) {
                Log::warning('No se pudo guardar XML/PDF', ['error' => $e->getMessage()]);
            }

            return back()
                ->with('success', "Factura generada. Folio fiscal: " . ($respuesta['uuid'] ?? 'N/D') . " | Total: $" . number_format($respuesta['total'] ?? 0, 2))
                ->with('factura_id', $factura->id);
        } catch (RequestException $e) {
            $errorApi = $e->response->json();
            Log::error('Error al facturar', ['detalle' => $errorApi]);
            $mensajeSat = $errorApi['message'] ?? '';
            $mensajeAmigable = 'No se pudo generar la factura. ';
            if (str_contains($mensajeSat, 'DomicilioFiscalReceptor') || str_contains($mensajeSat, 'Rfc')) {
                $mensajeAmigable .= 'Verifica que el RFC, nombre y CP coincidan con la Constancia.';
            } elseif (str_contains($mensajeSat, 'RegimenFiscal')) {
                $mensajeAmigable .= 'El régimen fiscal no corresponde al RFC.';
            } else {
                $mensajeAmigable .= 'Revisa los datos fiscales. ' . $mensajeSat;
            }
            return back()->withInput()->with('error', $mensajeAmigable);
        }
    }

    // Descargas
    public function descargarPdf($id, FacturapiService $facturapi)
    {
        $factura = Factura::findOrFail($id);
        return response($facturapi->descargarPdf($factura->facturapi_id), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="factura-' . $factura->folio_reservacion . '.pdf"');
    }

    public function descargarXml($id, FacturapiService $facturapi)
    {
        $factura = Factura::findOrFail($id);
        return response($facturapi->descargarXml($factura->facturapi_id), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="factura-' . $factura->folio_reservacion . '.xml"');
    }

    // Envio
    public function enviarCorreo(Request $request, $id, FacturapiService $facturapi)
    {
        $factura = Factura::findOrFail($id);

        if (empty($factura->facturapi_id)) {
            return back()->with('error', 'Esta factura no tiene folio de Facturapi.');
        }

        // Validar el correo (opcional: si no mandan, usa el de la factura)
        $datos = $request->validate([
            'correo_destino' => 'nullable|email',
        ], [
            'correo_destino.email' => 'El correo no tiene un formato válido.',
        ]);

        try {
            // Si escribieron un correo, se usa ese; si no, el de la factura original
            $correo = $datos['correo_destino'] ?? null;
            $facturapi->enviarPorCorreo($factura->facturapi_id, $correo);

            $mensaje = $correo
                ? "Factura enviada a {$correo}."
                : "Factura reenviada al correo registrado.";

            return back()->with('success', $mensaje);
        } catch (RequestException $e) {
            Log::error('Error al enviar factura', ['detalle' => $e->response->json()]);
            return back()->with('error', "No se pudo enviar: " . ($e->response->json()['message'] ?? 'error.'));
        }
    }

    // Cancelar
    public function cancelar(Request $request, $id, FacturapiService $facturapi)
    {
        $factura = Factura::findOrFail($id);

        if ($factura->estatus === 'cancelada') {
            return back()->with('error', 'Esta factura ya está cancelada.');
        }
        if (empty($factura->facturapi_id)) {
            return back()->with('error', 'Esta factura no tiene folio de Facturapi.');
        }

        $datos = $request->validate([
            'motivo'       => 'required|in:01,02,03,04',
            'sustituto_id' => 'required_if:motivo,01',
        ], [
            'sustituto_id.required_if' => 'Para el motivo 01 debes indicar el folio de la factura que sustituye a esta.',
        ]);

        try {
            $sustitutoId = $datos['motivo'] === '01' ? $datos['sustituto_id'] : null;

            $facturapi->cancelarFactura($factura->facturapi_id, $datos['motivo'], $sustitutoId);

            $factura->update([
                'estatus' => 'cancelada',
                'status'  => 'canceled',
            ]);

            return back()->with('success', 'Factura cancelada correctamente (motivo ' . $datos['motivo'] . ').');
        } catch (RequestException $e) {
            $errorApi = $e->response->json();
            Log::error('Error al cancelar', ['detalle' => $errorApi]);

            $msg = $errorApi['message'] ?? 'error desconocido';
            if ($datos['motivo'] === '01') {
                $msg .= ' — Nota: el motivo 01 a veces es rechazado por el SAT. Si persiste, intenta el motivo 02.';
            }
            return back()->with('error', 'No se pudo cancelar: ' . $msg);
        }
    }

    // ============================================
    // LISTADO (vista con tabla, si la usas)
    // ============================================
    public function index()
    {
        $facturas = DB::table('facturas')->orderBy('id', 'desc')->paginate(20);
        return view('Admin.listadoFacturas', compact('facturas'));
    }

    // ============================================
    // APIs PARA LA VISTA DE SELECCIÓN (JSON)
    // ============================================

    // Reservaciones (con flag de facturada)
    public function apiReservaciones()
    {
        $reservaciones = DB::table('reservaciones')
            ->orderBy('id_reservacion', 'desc')
            ->get();

        $facturados = DB::table('facturas')
            ->where('estatus', 'timbrada')
            ->pluck('folio_reservacion')
            ->toArray();

        $reservaciones->each(function ($r) use ($facturados) {
            $r->facturada = in_array($r->codigo, $facturados);
        });

        return response()->json($reservaciones);
    }

    // Folios ya facturados (para cruzar con contratos)
    public function apiFoliosFacturados()
    {
        $facturados = DB::table('facturas')
            ->where('estatus', 'timbrada')
            ->pluck('folio_reservacion')
            ->toArray();

        return response()->json($facturados);
    }

    // Una reservación para pre-llenar
    public function apiReservacion($folio)
    {
        $reservacion = DB::table('reservaciones')->where('codigo', $folio)->first();
        return response()->json($reservacion);
    }

    // Facturas timbradas
    public function apiFacturadas()
    {
        $facturas = DB::table('facturas')
            ->where('estatus', 'timbrada')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($facturas);
    }

    // Facturas canceladas
    public function apiCanceladas()
    {
        $facturas = DB::table('facturas')
            ->where('estatus', 'cancelada')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($facturas);
    }
}
