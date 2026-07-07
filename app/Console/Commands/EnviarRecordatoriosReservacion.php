<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecordatorioReservacionMail;

class EnviarRecordatoriosReservacion extends Command
{
    private const TZ = 'America/Mexico_City';
    private const ESTADOS_VIGENTES = ['confirmada', 'prepago', 'pendiente_pago'];

    /**
     * A QUIÉN llega el recordatorio:
     *   - Con una dirección aquí  -> llega SIEMPRE a esa dirección (la empresa), NO al cliente.
     *   - Vacío ('')              -> llega al cliente (email_cliente), como estaba antes.
     * Para volver a mandárselo al cliente, deja esto en '' (cadena vacía).
     */
    private const DESTINO = 'reservaciones@viajerocarental.com';

    /**
     * --horas:   anticipación del recordatorio (por defecto 1 h antes del retiro).
     * --ventana: ancho de la franja en minutos. DEBE ser >= al intervalo de tu cron.
     */
    protected $signature = 'reservaciones:recordatorios {--horas=1} {--ventana=18}';

    protected $description = 'Envía recordatorios de reservaciones próximas a su retiro (no escribe nada en la BD)';

    public function handle(): int
    {
        $horas   = max(0, (int) $this->option('horas'));
        $ventana = max(1, (int) $this->option('ventana'));

        $ahora = Carbon::now(self::TZ);
        $desde = $ahora->copy()->addHours($horas);
        $hasta = $desde->copy()->addMinutes($ventana);

        Log::info('[RECORDATORIO] Ventana calculada', [
            'ahora' => $ahora->toDateTimeString(),
            'desde' => $desde->toDateTimeString(),
            'hasta' => $hasta->toDateTimeString(),
        ]);

        // Acotamos por la fecha del retiro (usa el índice). fecha_inicio es DATE.
        $reservaciones = DB::table('reservaciones')
            ->whereIn('estado', self::ESTADOS_VIGENTES)
            ->whereBetween('fecha_inicio', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha_inicio')
            ->get();

        $enviados = 0;

        foreach ($reservaciones as $r) {
            $retiro = $this->parseRetiro((string) $r->fecha_inicio, $r->hora_retiro);
            if (!$retiro) {
                continue;
            }

            // Solo las que caen dentro de la franja [desde, hasta)
            if ($retiro->lt($desde) || $retiro->gte($hasta)) {
                continue;
            }

            // Dirección fija (empresa) o, si la constante está vacía, el correo del cliente.
            $destino = self::DESTINO !== '' ? self::DESTINO : (string) $r->email_cliente;
            if (trim($destino) === '') {
                continue;
            }

            $ubicacion = $this->ubicacionRetiro($r->sucursal_retiro);

            try {
                Mail::to($destino)
                    ->send(new RecordatorioReservacionMail($r, $ubicacion, $retiro));

                $enviados++;
                Log::info('[RECORDATORIO] Enviado', [
                    'id_reservacion' => $r->id_reservacion,
                    'folio'          => $r->codigo,
                    'destino'        => $destino,
                    'retiro'         => $retiro->format('Y-m-d H:i'),
                ]);
            } catch (\Throwable $e) {
                Log::error('[RECORDATORIO] Falló el envío', [
                    'id_reservacion' => $r->id_reservacion,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        $this->info("Recordatorios enviados: {$enviados}");
        return self::SUCCESS;
    }

    /**
     * Interpreta fecha_inicio (DATE) + hora_retiro (TIME) SIEMPRE en hora de México.
     * El 3er parámetro de createFromFormat fuerza la zona horaria, de modo que la
     * hora NO se malinterprete como UTC (que era lo que descartaba las reservas).
     */
    private function parseRetiro(string $fecha, ?string $hora): ?Carbon
    {
        $fecha = trim($fecha);
        if ($fecha === '') {
            return null;
        }

        $hora = trim((string) $hora);
        if ($hora === '') {
            $hora = '00:00:00';
        } elseif (preg_match('/^\d{1,2}:\d{2}$/', $hora)) {
            $hora .= ':00';
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', "{$fecha} {$hora}", self::TZ);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ubicacionRetiro($sucursalId): string
    {
        if (!$sucursalId) {
            return '-';
        }

        $info = DB::table('sucursales as s')
            ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
            ->where('s.id_sucursal', $sucursalId)
            ->select('s.nombre as sucursal', 'c.nombre as ciudad')
            ->first();

        return $info ? "{$info->ciudad} - {$info->sucursal}" : '-';
    }
}