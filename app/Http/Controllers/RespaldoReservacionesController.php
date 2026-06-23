<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReservacionesBackupExport;
use App\Imports\ReservacionesBackupImport;

class RespaldoReservacionesController extends Controller
{
    /**
     * ⬇️ Exporta TODA la base (5 tablas) a un .xlsx con 5 hojas.
     */
    public function exportar()
    {
        try {
            $fecha  = now()->format('Y-m-d_H-i');
            $nombre = "ViajeroCar_reservaciones_backup_{$fecha}.xlsx";

            return Excel::download(new ReservacionesBackupExport, $nombre);

        } catch (\Throwable $e) {
            Log::error('❌ Error al exportar respaldo de reservaciones: ' . $e->getMessage());
            return back()->with('error', 'No se pudo generar el respaldo: ' . $e->getMessage());
        }
    }

    /**
     * 📥 Importa el .xlsx y restaura/actualiza por ID.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'El archivo debe ser .xlsx o .xls.',
        ]);

        try {
            // Reiniciar contadores
            ReservacionesBackupImport::$resumen = [];

            Excel::import(new ReservacionesBackupImport, $request->file('archivo'));

            // Armar mensaje de resumen
            $partes = [];
            foreach (ReservacionesBackupImport::$resumen as $tabla => $r) {
                $partes[] = "{$tabla}: {$r['insertados']} nuevas, {$r['actualizados']} actualizadas, {$r['saltados']} saltadas";
            }

            $mensaje = 'Importación completada. ' . implode(' | ', $partes);

            return back()->with('success', $mensaje);

        } catch (\Throwable $e) {
            Log::error('❌ Error al importar respaldo de reservaciones: ' . $e->getMessage());
            return back()->with('error', 'No se pudo importar el respaldo: ' . $e->getMessage());
        }
    }
}
