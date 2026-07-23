<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecordatorioReservacionMail;

class EnviarRecordatoriosReservacion extends Command
{
    private const TZ = 'America/Mexico_City';
    private const ESTADOS_VIGENTES = ['confirmada', 'prepago', 'pendiente_pago'];

    private const DESTINO = 'reservaciones@viajerocarental.com';

    protected $signature = 'reservaciones:recordatorios {--horas=1}';

    protected $description = 'Envía recordatorios de reservaciones próximas a su retiro (no escribe nada en la BD)';

    public function handle(): int
    {
        $horas = max(0, (int) $this->option('horas'));

        $ahora  = Carbon::now(self::TZ);
        $limite = $ahora->copy()->addHours($horas);

        Log::info('[RECORDATORIO] Rango calculado', [
            'ahora'  => $ahora->toDateTimeString(),
            'limite' => $limite->toDateTimeString(),
        ]);

        $reservaciones = DB::table('reservaciones')
            ->whereIn('estado', self::ESTADOS_VIGENTES)
            ->whereBetween('fecha_inicio', [$ahora->toDateString(), $limite->toDateString()])
            ->orderBy('fecha_inicio')
            ->get();

        $enviados = 0;

        foreach ($reservaciones as $r) {
            $retiro = $this->parseRetiro((string) $r->fecha_inicio, $r->hora_retiro);
            if (!$retiro) {
                continue;
            }

            // Rango abierto: todo lo que sale entre ahora y ahora + N horas
            if ($retiro->lt($ahora) || $retiro->gt($limite)) {
                continue;
            }

            $destino = self::DESTINO !== '' ? self::DESTINO : (string) $r->email_cliente;
            if (trim($destino) === '') {
                continue;
            }

            // Candado en caché: add() solo escribe si la llave NO existe.
            // Si ya existe, esta reservación ya recibió su recordatorio.
            $candado = "recordatorio:{$r->id_reservacion}";
            if (! Cache::add($candado, 1, now()->addHours(48))) {
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
                // Libera el candado para que el siguiente ciclo reintente
                Cache::forget($candado);
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