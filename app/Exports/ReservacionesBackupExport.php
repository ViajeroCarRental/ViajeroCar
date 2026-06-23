<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * 📦 Respaldo COMPLETO de reservaciones + tablas anexas.
 * Genera un .xlsx con 5 hojas (una por tabla).
 * Conserva IDs y FKs tal cual para poder reimportar idéntico.
 */
class ReservacionesBackupExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TablaSheet('reservaciones', [
                'id_reservacion','id_usuario','id_asesor','id_vehiculo','id_categoria',
                'ciudad_retiro','ciudad_entrega','sucursal_retiro','sucursal_entrega',
                'fecha_inicio','hora_retiro','fecha_fin','hora_entrega','estado',
                'aprobado_por_superadmin','hold_expires_at','subtotal','impuestos','total','moneda',
                'tarifa_ajustada','tarifa_modificada','tarifa_base','horas_cortesia',
                'no_vuelo','codigo','nombre_cliente','apellidos_cliente','email_cliente','telefono_cliente',
                'paypal_order_id','status_pago','metodo_pago','firma_arrendador',
                'delivery_activo','delivery_ubicacion','delivery_direccion','delivery_km',
                'delivery_precio_km','delivery_total','created_at','updated_at',
            ]),

            new TablaSheet('reservacion_servicio', [
                'id','id_reservacion','id_servicio','id_contrato',
                'cantidad','precio_unitario','created_at','updated_at',
            ]),

            new TablaSheet('reservacion_seguro', [
                'id','id_reservacion','id_seguro','precio_por_dia','created_at','updated_at',
            ]),

            new TablaSheet('reservacion_paquete_seguro', [
                'id','id_reservacion','id_paquete','precio_por_dia','created_at','updated_at',
            ]),

            new TablaSheet('reservacion_seguro_individual', [
                'id','id_reservacion','id_individual','precio_por_dia','cantidad','created_at','updated_at',
            ]),
        ];
    }
}

/**
 * Hoja genérica: toma una tabla y sus columnas, y vuelca todo tal cual.
 */
class TablaSheet implements FromCollection, WithHeadings, WithTitle
{
    protected string $tabla;
    protected array $columnas;

    public function __construct(string $tabla, array $columnas)
    {
        $this->tabla    = $tabla;
        $this->columnas = $columnas;
    }

    public function collection()
    {
        return DB::table($this->tabla)
            ->select($this->columnas)
            ->orderBy($this->columnas[0]) // ordena por la PK (primera columna)
            ->get()
            ->map(function ($row) {
                // Convertir el objeto a array en el MISMO orden de columnas
                $fila = [];
                foreach ($this->columnas as $col) {
                    $fila[] = $row->{$col} ?? null;
                }
                return $fila;
            });
    }

    public function headings(): array
    {
        return $this->columnas;
    }

    public function title(): string
    {
        // Excel limita el nombre de hoja a 31 caracteres
        return substr($this->tabla, 0, 31);
    }
}
