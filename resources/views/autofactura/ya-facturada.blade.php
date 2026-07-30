<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ya facturada - Viajero Car</title>
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
            margin-bottom: 2rem;
            font-size: 1.05rem;
            line-height: 1.5;
        }

        .body strong {
            color: var(--text-main);
        }

        /* =========================================
           Botones
           ========================================= */
        .btns {
            display: flex;
            gap: 1rem;
        }

        .btn {
            flex: 1;
            padding: 0.9rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-size: 1rem;
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
                <h2>Esta renta ya fue facturada</h2>
            </div>
            
            <div class="body">
                <p>El folio <strong>{{ $factura->folio_reservacion }}</strong> ya tiene una factura generada. Puedes descargarla en los siguientes formatos:</p>
                
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