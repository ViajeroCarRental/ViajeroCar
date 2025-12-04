<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PolizaVencimientoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vehiculo;
    public $diasRestantes;

    public function __construct($vehiculo, $diasRestantes)
    {
        $this->vehiculo = $vehiculo;
        $this->diasRestantes = $diasRestantes;
    }

    public function build()
    {
        return $this->subject('⚠️ Aviso: Póliza de seguro próxima a vencer')
                    ->view('emails.poliza_vencimiento');
    }
}
