<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Viajero Car</title>
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
            --border-color: #d1d5db;
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
        }

        .header {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-dark));
            color: #ffffff;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .header p {
            opacity: 0.85;
            font-size: 0.95rem;
        }

        .body {
            padding: 2rem;
        }

        /* =========================================
           Alertas
           ========================================= */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            background: #fef2f2;
            color: var(--primary-red-dark);
            border: 1px solid #fca5a5;
        }
        
        .alert div { margin-bottom: 0.3rem; }
        .alert div:last-child { margin-bottom: 0; }

        /* =========================================
           Formulario
           ========================================= */
        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .input-group input {
            width: 100%;
            padding: 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            color: var(--text-main);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
        }
        
        .input-group input::placeholder {
            color: #9ca3af;
        }

        /* =========================================
           Botón
           ========================================= */
        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-red);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: var(--primary-red-dark);
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
                <i class="fas fa-file-invoice-dollar"></i>
                <h2>Factura tu Renta</h2>
                <p>Ingresa los datos de tu reservación</p>
            </div>
            
            <div class="body">
                {{-- Alertas de Error --}}
                @if(session('error'))
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert">
                        @foreach($errors->all() as $e)
                            <div><i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle; margin-right: 4px;"></i> {{ $e }}</div>
                        @endforeach
                    </div>
                @endif

                {{-- Formulario --}}
                <form action="{{ route('autofactura.validar') }}" method="POST">
                    @csrf
                    
                    <div class="input-group">
                        <label for="folio">Folio de Reservación</label>
                        <input type="text" id="folio" name="folio" required
                               value="{{ old('folio') }}" placeholder="Ej. R-2025-00123">
                    </div>
                    
                    <div class="input-group">
                        <label for="correo">Correo con el que reservaste</label>
                        <input type="email" id="correo" name="correo" required
                               value="{{ old('correo') }}" placeholder="tucorreo@ejemplo.com">
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-search"></i> Buscar mi reservación
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>