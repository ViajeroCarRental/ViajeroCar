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
    public $seguroReserva;
    public $extrasReserva;
    public $lugarRetiro;
    public $lugarEntrega;
    public $imgCategoria;
    public $opcionesRentaTotal;
    public $tuAuto;


    public function __construct(
        $reservacion,
        $categoria,
        $seguroReserva = null,
        $extrasReserva = null,
        $lugarRetiro = '-',
        $lugarEntrega = '-',
        $imgCategoria = null,
        $opcionesRentaTotal = 0,
        $tuAuto = []
    ) {
        $this->reservacion = $reservacion;
        $this->categoria   = $categoria;
        $this->seguroReserva = $seguroReserva;
        $this->extrasReserva = $extrasReserva;
        $this->lugarRetiro = $lugarRetiro;
        $this->lugarEntrega = $lugarEntrega;
        $this->imgCategoria = $imgCategoria;
        $this->opcionesRentaTotal = $opcionesRentaTotal;
        $this->tuAuto = $tuAuto;

    }

    public function build()
    {
        return $this
            ->subject("Nueva reservación {$this->reservacion->codigo} - Pago en mostrador")
            ->view('emails.reservacionesAdmin');
    }
}
