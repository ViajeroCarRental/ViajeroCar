<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura generada - Viajero Car</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* =========================================
           Variables (Tema Rojo)
           ========================================= */
        :root {
            --primary-red: #dc2626;       /* Rojo principal */
            --primary-red-dark: #b91c1c;  /* Rojo oscuro */
            --text-main: #374151;
            --text-muted: #6b7280;
            --bg-body: #f3f4f6;
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--bg-body);
            padding: 1rem;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* =========================================
           Contenedor y Tarjeta
           ========================================= */
        .wrap {
            width: 100%;
            max-width: 480px;
            margin: auto;
        }

        .card {
            background: #ffffff;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-dark));
            color: #ffffff;
            padding: 2.5rem 2rem;
        }

        .header i {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0;
        }

        .body {
            padding: 2.5rem 2rem;
        }

        .body p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
        }

        /* =========================================
           Datos de la Factura
           ========================================= */
        .datos {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 10px;
            padding: 1.2rem;
            margin-bottom: 2rem;
            text-align: left;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        .datos div {
            padding: 0.4rem 0;
            border-bottom: 1px solid #fecaca;
        }

        .datos div:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .datos strong {
            color: var(--primary-red-dark);
            display: inline-block;
            min-width: 85px;
        }

        /* =========================================
           Botones
           ========================================= */
        .btns {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        /* Botón PDF (Primario) */
        .btn-pdf {
            background: var(--primary-red);
            color: #ffffff;
            border: 2px solid var(--primary-red);
        }

        .btn-pdf:hover {
            background: var(--primary-red-dark);
            border-color: var(--primary-red-dark);
            transform: translateY(-1px);
        }

        /* Botón XML (Secundario / Outline) */
        .btn-xml {
            background: transparent;
            color: var(--primary-red);
            border: 2px solid var(--primary-red);
        }

        .btn-xml:hover {
            background: #fef2f2;
            color: var(--primary-red-dark);
            border-color: var(--primary-red-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <i class="fas fa-check-circle"></i>
                <h2>¡Factura generada!</h2>
            </div>
            
            <div class="body">
                <p>Tu factura se envió a tu correo. También puedes descargarla aquí:</p>
                
                <div class="datos">
                    <div><strong>Folio:</strong> {{ $factura->folio_reservacion }}</div>
                    <div style="word-break: break-all;"><strong>Folio fiscal:</strong> {{ $factura->uuid }}</div>
                    <div><strong>Total:</strong> ${{ number_format($factura->total, 2) }}</div>
                </div>
                
                <div class="btns">
                    <a href="{{ route('autofactura.pdf', $factura->id) }}" class="btn btn-pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="{{ route('autofactura.xml', $factura->id) }}" class="btn btn-xml">
                        <i class="fas fa-file-code"></i> XML
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>