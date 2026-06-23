<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * 📥 Importa el respaldo .xlsx de reservaciones + tablas anexas.
 * Compara por ID: si existe lo actualiza, si no lo inserta (updateOrInsert).
 * Desactiva FOREIGN_KEY_CHECKS durante la carga y los reactiva al final.
 * Orden: reservaciones (padre) primero, luego las 4 anexas (hijas).
 */
class ReservacionesBackupImport implements WithMultipleSheets
{
    // Contadores para el reporte final
    public static array $resumen = [];

    public function sheets(): array
    {
        // El mapeo asocia el TÍTULO de la hoja con la clase que la procesa.
        return [
            'reservaciones'                 => new TablaImport('reservaciones', 'id_reservacion'),
            'reservacion_servicio'          => new TablaImport('reservacion_servicio', 'id'),
            'reservacion_seguro'            => new TablaImport('reservacion_seguro', 'id'),
            'reservacion_paquete_seguro'    => new TablaImport('reservacion_paquete_seguro', 'id'),
            'reservacion_seguro_individual' => new TablaImport('reservacion_seguro_individual', 'id'),
        ];
    }
}

/**
 * Procesa una hoja: hace updateOrInsert por la PK indicada.
 */
class TablaImport implements ToArray, WithHeadingRow
{
    protected string $tabla;
    protected string $pk;

    public function __construct(string $tabla, string $pk)
    {
        $this->tabla = $tabla;
        $this->pk    = $pk;
    }

    public function array(array $filas)
    {
        $insertados   = 0;
        $actualizados = 0;
        $saltados     = 0;

        // 🔒 Desactivar checks de FK durante la carga de ESTA hoja
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($filas as $fila) {
                // WithHeadingRow ya entrega claves = encabezados de columna.
                // 🔧 OPCIÓN A: normalizamos celdas vacías a null y luego
                //    QUITAMOS las claves null para no chocar con columnas NOT NULL.
                //    Las columnas que no se manden tomarán su valor DEFAULT en la DB.
                $fila = array_map(function ($v) {
                    // Normalizar celdas vacías a null
                    if ($v === '' || $v === null) return null;
                    return $v;
                }, $fila);

                // Si no hay PK en la fila, se salta (fila vacía)
                if (!isset($fila[$this->pk]) || $fila[$this->pk] === null) {
                    $saltados++;
                    continue;
                }

                $idValor = $fila[$this->pk];

                // 🔧 OPCIÓN A: eliminar todas las claves con valor null.
                //    Así no enviamos null a columnas NOT NULL (ej. aprobado_por_superadmin,
                //    tarifa_ajustada, delivery_activo, estado, etc.) y dejamos que la DB
                //    aplique sus defaults.
                $fila = array_filter($fila, function ($v) {
                    return $v !== null;
                });

                // Seguridad extra: la PK SIEMPRE debe ir en el update/insert.
                $fila[$this->pk] = $idValor;

                // ¿Ya existe ese ID?
                $existe = DB::table($this->tabla)
                    ->where($this->pk, $idValor)
                    ->exists();

                // updateOrInsert: compara por PK, actualiza o inserta
                DB::table($this->tabla)->updateOrInsert(
                    [$this->pk => $idValor],
                    $fila
                );

                $existe ? $actualizados++ : $insertados++;
            }
        } finally {
            // 🔓 Reactivar checks SIEMPRE, aunque truene algo
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        ReservacionesBackupImport::$resumen[$this->tabla] = [
            'insertados'   => $insertados,
            'actualizados' => $actualizados,
            'saltados'     => $saltados,
        ];
    }
}
