<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class FacturapiService
{
    protected function client(): PendingRequest
    {
        return Http::withBasicAuth(config('services.facturapi.key'), '')
            ->baseUrl(config('services.facturapi.url'))
            ->acceptJson();
    }

    public function validarCliente(array $data): array
    {
        return $this->client()
            ->post('/customers/validate', $data)
            ->throw()
            ->json();
    }

    public function crearFactura(array $data): array
    {
        return $this->client()->post('/invoices', $data)->throw()->json();
    }

    public function descargarPdf(string $invoiceId): string
    {
        return $this->client()->get("/invoices/{$invoiceId}/pdf")->throw()->body();
    }

    public function descargarXml(string $invoiceId): string
    {
        return $this->client()->get("/invoices/{$invoiceId}/xml")->throw()->body();
    }

    public function enviarPorCorreo(string $invoiceId, ?string $email = null): array
    {
        $payload = $email ? ['email' => $email] : [];
        return $this->client()->post("/invoices/{$invoiceId}/email", $payload)->throw()->json();
    }

    public function listarFacturas(array $params = []): array
    {
        return $this->client()->get('/invoices', $params)->throw()->json();
    }

    public function cancelarFactura(string $invoiceId, string $motivo = '02', ?string $sustitutoId = null): array
    {
        $url = "/invoices/{$invoiceId}?motive={$motivo}";
        if ($sustitutoId) {
            $url .= "&substitution={$sustitutoId}";
        }
        return $this->client()->delete($url)->throw()->json();
    }
}
