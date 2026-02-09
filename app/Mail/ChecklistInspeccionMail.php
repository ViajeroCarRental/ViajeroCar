<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChecklistInspeccionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Reservación relacionada al contrato.
     */
    public $reservacion;

    /**
     * Contrato relacionado al checklist.
     */
    public $contrato;

    /**
     * Tipo de checklist: 'salida' o 'entrada'.
     */
    public $tipo;

    /**
     * PDF para el cliente (checklist con datos omitidos).
     */
    public $pdfCliente;

    /**
     * PDF interno (checklist completo).
     */
    public $pdfInterno;

    /**
     * Fotos de la inspección (solo para mostrarlas en la vista,
     * ya NO se adjuntan como archivos).
     */
    public $fotos;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(
        $reservacion,
        $contrato,
        string $tipo = 'salida',
        ?string $pdfCliente = null,
        ?string $pdfInterno = null,
        array $fotos = []
    ) {
        $this->reservacion = $reservacion;
        $this->contrato    = $contrato;
        $this->tipo        = $tipo;
        $this->pdfCliente  = $pdfCliente;
        $this->pdfInterno  = $pdfInterno;
        $this->fotos       = $fotos;   // solo para la vista del correo
    }

    /**
     * Asunto del correo.
     */
    public function envelope(): Envelope
    {
        $sufijo = $this->tipo === 'entrada' ? ' (regreso)' : ' (salida)';

        return new Envelope(
            subject: 'Checklist de inspección de vehículo' . $sufijo,
        );
    }

    /**
     * Contenido (vista) del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checklist_correo',
            with: [
                'reservacion' => $this->reservacion,
                'contrato'    => $this->contrato,
                'tipo'        => $this->tipo,
                'fotos'       => $this->fotos,  // si quieres usarlas en el cuerpo
            ],
        );
    }

    /**
     * Adjuntos del correo: solo PDFs.
     */
    public function attachments(): array
    {
        $attachments = [];

        // PDF que va al cliente
        if (!empty($this->pdfCliente)) {
            $nombre = 'Checklist-' . $this->tipo . '-cliente.pdf';

            $attachments[] = Attachment::fromData(
                fn () => $this->pdfCliente,
                $nombre
            )->withMime('application/pdf');
        }

        // PDF interno (si decides mandarlo en este mismo correo)
        if (!empty($this->pdfInterno)) {
            $nombreInterno = 'Checklist-' . $this->tipo . '-interno.pdf';

            $attachments[] = Attachment::fromData(
                fn () => $this->pdfInterno,
                $nombreInterno
            )->withMime('application/pdf');
        }

        // 🔥 YA NO SE ADJUNTAN FOTOS 🔥

        return $attachments;
    }
}
