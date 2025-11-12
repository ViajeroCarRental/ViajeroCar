<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PolizaVencimientoMail;
use Carbon\Carbon;

class RevisarPolizas extends Command
{
    protected $signature = 'polizas:revisar';
    protected $description = 'Revisa las pólizas próximas a vencer o vencidas y envía notificaciones por correo.';

    public function handle()
    {
        $hoy = Carbon::now();

        // 🔹 Obtener pólizas con fecha de fin de vigencia
        $polizas = DB::table('vehiculos')
            ->whereNotNull('fin_vigencia_poliza')
            ->get();

        foreach ($polizas as $p) {
            $fin = Carbon::parse($p->fin_vigencia_poliza);
            $dias = $hoy->diffInDays($fin, false);

            if ($dias <= 9) {

                    $to = env('POLIZAS_TO', 'reservaciones@viajerocarental.com');

                    $ccList = collect(explode(',', env('POLIZAS_CC', '')))
                        ->map(fn($email) => trim($email))
                        ->filter()
                        ->toArray();

                    // 🧩 Línea para verificar en consola qué CC está detectando
                    $this->info('CC detectados: ' . json_encode($ccList));

                    $mail = Mail::to($to);
                    if (!empty($ccList)) {
                        $mail->bcc($ccList); // ← cambia a copia oculta
                    }


                    $mail->send(new PolizaVencimientoMail($p, $dias));

                    $this->info("📨 Correo enviado por póliza {$p->no_poliza} ({$dias} días restantes)");
                }

        }

        $this->info("✅ Revisión completada. Se notificaron las pólizas vencidas o próximas a vencer.");
    }
}
