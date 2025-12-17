<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CotizacionAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clienteNombre;
    public string $folio;

    protected string $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(string $clienteNombre, string $folio, string $pdfPath)
    {
        $this->clienteNombre = $clienteNombre;
        $this->folio = $folio;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Tu cotización {$this->folio} - Viajero Car Rental")
            ->view('emails.cotizacionesAdmin')
            ->with([
                'clienteNombre' => $this->clienteNombre,
                'folio'         => $this->folio,
            ])
            ->attach($this->pdfPath);
    }
}
