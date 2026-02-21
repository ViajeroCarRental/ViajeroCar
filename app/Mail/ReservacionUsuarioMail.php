<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservacionUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservacion;
    public $tipo;               // 'mostrador' | 'linea' | 'en_linea'

    // 👇 Datos extra igual que en el mail de Admin (sin seguros)
    public $categoria;
    public $extrasReserva;
    public $lugarRetiro;
    public $lugarEntrega;
    public $imgCategoria;
    public $opcionesRentaTotal;
    public $tuAuto;

    /**
     * @param  mixed       $reservacion
     * @param  string      $tipo                  'mostrador' | 'linea' | 'en_linea'
     * @param  mixed|null  $categoria
     * @param  mixed|null  $extrasReserva
     * @param  string      $lugarRetiro
     * @param  string      $lugarEntrega
     * @param  string|null $imgCategoria
     * @param  float|int   $opcionesRentaTotal
     * @param  array       $tuAuto
     */
    public function __construct(
        $reservacion,
        string $tipo,
        $categoria = null,
        $extrasReserva = null,
        $lugarRetiro = '-',
        $lugarEntrega = '-',
        $imgCategoria = null,
        $opcionesRentaTotal = 0,
        $tuAuto = []
    ) {
        $this->reservacion        = $reservacion;
        $this->tipo               = $tipo;
        $this->categoria          = $categoria;
        $this->extrasReserva      = $extrasReserva;
        $this->lugarRetiro        = $lugarRetiro;
        $this->lugarEntrega       = $lugarEntrega;
        $this->imgCategoria       = $imgCategoria;
        $this->opcionesRentaTotal = $opcionesRentaTotal;
        $this->tuAuto             = $tuAuto;
    }

    public function build()
    {
        return $this
            ->subject(
                // Aceptamos 'linea' o 'en_linea' como pago en línea
                ($this->tipo === 'linea' || $this->tipo === 'en_linea')
                    ? "Reserva confirmada {$this->reservacion->codigo}"
                    : "Confirmación de reservación {$this->reservacion->codigo}"
            )
            ->view('emails.reservacionesUsu')
            ->with([
                'reservacion'        => $this->reservacion,
                'tipo'               => $this->tipo,
                'categoria'          => $this->categoria,
                'extrasReserva'      => $this->extrasReserva,
                'lugarRetiro'        => $this->lugarRetiro,
                'lugarEntrega'       => $this->lugarEntrega,
                'imgCategoria'       => $this->imgCategoria,
                'opcionesRentaTotal' => $this->opcionesRentaTotal,
                'tuAuto'             => $this->tuAuto,
            ]);
    }
}
