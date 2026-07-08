<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RecordatorioReservacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public object $reservacion,
        public string $ubicacionRetiro,
        public Carbon $fechaRetiro
    ) {}

    public function build()
    {
        return $this
            ->subject('Recordatorio de tu reservación ' . $this->reservacion->codigo)
            ->view('emails.recordatorio-reservacion');
    }
}