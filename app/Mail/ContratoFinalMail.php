<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratoFinalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contrato;
    public $reservacion;
    public $licencia;
    public $vehiculo;
    public $dias;
    public $totalFinal;
    public $pdfContenido;   // ⬅ CAMBIO: antes era $pdfPath (una ruta de archivo)
    public $aviso;

    public function __construct(
        $contrato,
        $reservacion,
        $licencia,
        $vehiculo,
        $dias,
        $totalFinal,
        $pdfContenido,      // ⬅ CAMBIO: ahora llega el binario del PDF
        $aviso
    ) {
        $this->contrato     = $contrato;
        $this->reservacion  = $reservacion;
        $this->licencia     = $licencia;
        $this->vehiculo     = $vehiculo;
        $this->dias         = $dias;
        $this->totalFinal   = $totalFinal;
        $this->pdfContenido = $pdfContenido;
        $this->aviso        = $aviso;
    }

    public function build()
    {
        return $this->subject('Contrato Final - Viajero Car Rental')
            ->view('emails.contrato-final')
            // ⬅ CAMBIO: attachData() adjunta desde memoria.
            // attach() esperaba una ruta y por eso fallaba con
            // "fopen(): $filename must not contain any null bytes".
            ->attachData(
                $this->pdfContenido,
                "Contrato_{$this->contrato->id_contrato}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
