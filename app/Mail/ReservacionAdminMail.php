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
    public $logoPath;
    public $autoPath;


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

        $this->logoPath = public_path('img/Logo3.jpg');
        $pathImagen = public_path(str_replace(config('app.url'), '', $this->imgCategoria));
        if (filter_var($imgCategoria, FILTER_VALIDATE_URL) === false) {
        $this->imgCategoria = url($imgCategoria);
    } else {
        $this->imgCategoria = $imgCategoria;
    }

    }

public function build()
{
    return $this->subject("Nueva reservación {$this->reservacion->codigo}")
                ->view('emails.reservacionesAdmin')
                ->with(['imgCategoria' => $this->imgCategoria]);
}
}
