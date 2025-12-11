<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class CierreContratoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservacion;
    public $contrato;
    public $pagos;
    public $servicios;
    public $segurosPaquete;
    public $segurosIndividuales;
    public $cargos;
    public $dias;
    public $vehiculo;

    public function __construct(
        $reservacion,
        $contrato,
        $pagos,
        $servicios,
        $segurosPaquete,
        $segurosIndividuales,
        $cargos,
        $dias,
        $vehiculo
    ) {
        // Determinar fecha de cierre correcta
        $fechaCierre = $contrato->cerrado_en
                        ? $contrato->cerrado_en
                        : $reservacion->fecha_fin;

        // Datos del contrato listos para PDF
        $this->reservacion = $reservacion;
        $this->contrato = (object)[
            'numero_contrato' => $contrato->numero_contrato ?? '',
            'cerrado_en'      => $fechaCierre,
        ];

        $this->pagos = $pagos;
        $this->servicios = $servicios;
        $this->segurosPaquete = $segurosPaquete;
        $this->segurosIndividuales = $segurosIndividuales;
        $this->cargos = $cargos;
        $this->dias = $dias;
        $this->vehiculo = $vehiculo;
    }

    public function build()
    {
        // 1) PDF — Desglose de pagos
        $pdfPagos = Pdf::loadView('Admin.cierre-pagos', [
            'reservacion' => $this->reservacion,
            'contrato' => $this->contrato,
            'pagos' => $this->pagos,
            'servicios' => $this->servicios,
            'segurosPaquete' => $this->segurosPaquete,
            'segurosIndividuales' => $this->segurosIndividuales,
            'cargos' => $this->cargos,
            'dias' => $this->dias
        ]);

        // 2) PDF — Ticket de cierre
        $pdfTicket = Pdf::loadView('Admin.cierre-ticket', [
            'reservacion' => $this->reservacion,
            'contrato' => $this->contrato,
            'vehiculo' => $this->vehiculo,
            'dias' => $this->dias
        ]);

        return $this->subject("Cierre de Contrato")
            ->view('emails.cierre-contrato')

            // Adjuntar PDF de desglose de pagos
            ->attachData(
                $pdfPagos->output(),
                "Desglose_Pagos_Contrato.pdf",
                ['mime' => 'application/pdf']
            )

            // Adjuntar ticket de cierre
            ->attachData(
                $pdfTicket->output(),
                "Ticket_Cierre_Contrato.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
