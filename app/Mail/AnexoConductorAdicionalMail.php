<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnexoConductorAdicionalMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Reservación relacionada al contrato.
     */
    public $reservacion;

    /**
     * Contrato principal.
     */
    public $contrato;

    /**
     * Rutas completas de los PDF de anexos a adjuntar.
     *
     * @var array<string>
     */
    public $rutasPdfs;

    /**
     * Create a new message instance.
     *
     * @param  mixed  $reservacion   // objeto de la consulta (DB::table...)
     * @param  mixed  $contrato      // objeto de la consulta (DB::table...)
     * @param  array  $rutasPdfs     // rutas completas a los PDF a adjuntar
     */
    public function __construct($reservacion, $contrato, array $rutasPdfs = [])
    {
        $this->reservacion = $reservacion;
        $this->contrato    = $contrato;
        $this->rutasPdfs   = $rutasPdfs;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $folio = $this->contrato->numero_contrato
            ?? $this->contrato->id_contrato
            ?? null;

        $subject = 'Anexos de Conductor Adicional - Viajero Car Rental';

        if ($folio) {
            $subject = "Anexos de Conductor Adicional – Contrato {$folio}";
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.anexos_conductores',
            with: [
                'reservacion' => $this->reservacion,
                'contrato'    => $this->contrato,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * Espera rutas completas a los PDFs en $this->rutasPdfs.
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->rutasPdfs as $ruta) {
            if (empty($ruta)) {
                continue;
            }

            $nombre = basename($ruta);

            $attachments[] = Attachment::fromPath($ruta)
                ->as($nombre)
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
