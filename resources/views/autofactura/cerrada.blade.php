<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación no disponible - Viajero Car</title>
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
            font-size: 1.05rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <i class="fas fa-clock"></i>
                <h2>Ya no es posible facturar en línea</h2>
            </div>
            
            <div class="body">
                <p>El contrato de esta reservación ya fue cerrado. Para solicitar tu factura, por favor contacta directamente a la oficina.</p>
            </div>
        </div>
    </div>
</body>
</html>