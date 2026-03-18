<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define el horario de los comandos de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 👇 Aquí agregas tus tareas automáticas
        $schedule->command('polizas:revisar')->dailyAt('08:00');
    }

    /**
     * Registra los comandos de consola para la aplicación.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

}
