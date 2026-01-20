<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CambioAutoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Reservación relacionada al contrato.
     */
    public $reservacion;

    /**
     * Contrato relacionado.
     */
    public $contrato;

    /**
     * PDF del cambio de auto (binario).
     */
    public $pdfCambio;

    /**
     * Fotos generales del cambio (lado derecho) para adjuntar.
     * Formato: [
     *   ['contenido' => ..., 'nombre' => ..., 'mime' => ...],
     *   ...
     * ]
     */
    public $fotosCambio;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(
        $reservacion,
        $contrato,
        ?string $pdfCambio = null,
        array $fotosCambio = []
    ) {
        $this->reservacion = $reservacion;
        $this->contrato    = $contrato;
        $this->pdfCambio   = $pdfCambio;
        $this->fotosCambio = $fotosCambio;
    }

    /**
     * Asunto del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cambio de vehículo en su contrato'
        );
    }

    /**
     * Contenido (vista) del correo.
     */
    public function content(): Content
    {
        $clienteNombre = trim(
            ($this->reservacion->nombre_cliente  ?? '') . ' ' .
            ($this->reservacion->apellidos_cliente ?? '')
        );

        $codigoReservacion =
            $this->reservacion->codigo_reservacion
            ?? $this->reservacion->codigo
            ?? $this->reservacion->id_reservacion;

        return new Content(
            view: 'emails.cambio_auto', // ← nombre de tu vista HTML de correo
            with: [
                'clienteNombre'    => $clienteNombre,
                'codigoReservacion'=> $codigoReservacion,
            ],
        );
    }

    /**
     * Adjuntos del correo: PDF + fotos del cambio.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // 📄 PDF del cambio de vehículo
        if (!empty($this->pdfCambio)) {
            $nombrePdf = 'Cambio-vehiculo-' . ($this->contrato->numero_contrato ?? 'contrato') . '.pdf';

            $attachments[] = Attachment::fromData(
                fn () => $this->pdfCambio,
                $nombrePdf
            )->withMime('application/pdf');
        }

        // 📷 Fotos del cambio (las del lado derecho)
        if (!empty($this->fotosCambio)) {
            foreach ($this->fotosCambio as $idx => $foto) {
                $contenido = $foto['contenido'] ?? null;

                if ($contenido === null) {
                    continue;
                }

                $nombre = $foto['nombre'] ?? ('foto-cambio-' . ($idx + 1) . '.jpg');
                $mime   = $foto['mime']   ?? 'image/jpeg';

                $attachments[] = Attachment::fromData(
                    fn () => $contenido,
                    $nombre
                )->withMime($mime);
            }
        }

        return $attachments;
    }
}
