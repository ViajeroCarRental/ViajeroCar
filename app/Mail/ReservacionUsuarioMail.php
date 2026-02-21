<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservacionUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservacion;
    public $tipo;

    public function __construct($reservacion, string $tipo)
    {
        $this->reservacion = $reservacion;
        $this->tipo = $tipo; // 'mostrador' | 'linea'
    }

    public function build()
    {
        return $this
            ->subject(
                $this->tipo === 'linea'
                    ? "Reserva confirmada {$this->reservacion->codigo}"
                    : "Confirmación de reservación {$this->reservacion->codigo}"
            )
            ->view('emails.reservacionesUsu')
            ->with([
                'reservacion' => $this->reservacion,
                'tipo'        => $this->tipo,
            ]);
    }
}
