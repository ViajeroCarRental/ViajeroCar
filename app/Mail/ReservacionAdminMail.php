<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservacionAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservacion;
    public $categoria;

    /**
     * @param  object  $reservacion
     * @param  object  $categoria
     */
    public function __construct($reservacion, $categoria)
    {
        $this->reservacion = $reservacion;
        $this->categoria   = $categoria;
    }

    public function build()
    {
        return $this
            ->subject("Nueva reservación {$this->reservacion->codigo} - Pago en mostrador")
            ->view('emails.reservacionesAdmin');
    }
}
