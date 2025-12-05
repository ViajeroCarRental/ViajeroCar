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
    public $pdfPath;
    public $aviso;
    public function __construct($contrato, $reservacion, $licencia, $vehiculo, $dias, $totalFinal, $pdfPath, $aviso)
    {
        $this->contrato    = $contrato;
        $this->reservacion = $reservacion;
        $this->licencia    = $licencia;
        $this->vehiculo    = $vehiculo;
        $this->dias        = $dias;
        $this->totalFinal  = $totalFinal;
        $this->pdfPath     = $pdfPath;
        $this->aviso       = $aviso;
    }

    public function build()
    {
        return $this->subject("Contrato Final - Viajero Car Rental")
            ->view('emails.contrato-final')   // ESTE ES EL RESUMEN
            ->attach($this->pdfPath, [
                'as' => "Contrato_{$this->contrato->id_contrato}.pdf",
                'mime' => 'application/pdf',
            ]);
    }
}
