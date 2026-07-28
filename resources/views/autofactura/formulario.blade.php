<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Facturación - Viajero Car</title>
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
        }

        /* =========================================
           Contenedor y Tarjeta
           ========================================= */
        .wrap {
            width: 100%;
            max-width: 640px;
            margin: 2rem auto;
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
        
        .alert ul { margin: 0; padding-left: 1.2rem; }
        .alert li { margin-bottom: 0.3rem; }
        .alert li:last-child { margin-bottom: 0; }

        /* =========================================
           Resumen de la Renta
           ========================================= */
        .resumen {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 10px;
            padding: 1.2rem;
            margin-bottom: 2rem;
        }

        .resumen h3 {
            font-size: 1rem;
            color: var(--primary-red-dark);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .resumen-row {
            display: flex;
            justify-content: space-between;
            padding: 0.3rem 0;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        .resumen-row .total {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-red-dark);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--bg-body);
            color: var(--text-main);
        }

        /* =========================================
           Formularios
           ========================================= */
        .input-group {
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .input-group input, 
        .input-group select {
            width: 100%;
            padding: 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            color: var(--text-main);
            background-color: #fff;
        }

        .input-group input:focus, 
        .input-group select:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
        }

        .input-group small {
            display: block;
            margin-top: 0.4rem;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* =========================================
           Botones y Notas
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
            margin-top: 1rem;
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

        .nota {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <i class="fas fa-file-invoice-dollar"></i>
                <h2>Datos de Facturación</h2>
                <p>Completa tus datos fiscales para generar tu CFDI 4.0</p>
            </div>

            <div class="body">

                {{-- Alertas --}}
                @if(session('error'))
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert">
                        <ul>
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Resumen de la renta (bloqueado, viene de la reservación) --}}
                <div class="resumen">
                    <h3><i class="fas fa-car"></i> Resumen de tu renta</h3>
                    <div class="resumen-row">
                        <span>Folio de reservación:</span>
                        <strong>{{ $reservacion->codigo }}</strong>
                    </div>
                    <div class="resumen-row">
                        <span>Total a facturar (IVA incl.):</span>
                        <span class="total">${{ number_format($reservacion->total, 2) }}</span>
                    </div>
                </div>

                <form action="{{ route('autofactura.timbrar') }}" method="POST">
                    @csrf

                    <div class="section-title">Tus Datos Fiscales</div>

                    <div class="input-group">
                        <label for="razon_social">Nombre o Razón Social *</label>
                        <input type="text" id="razon_social" name="razon_social" required
                               value="{{ old('razon_social', $datosPrevios->razon_social ?? '') }}"
                               placeholder="Como aparece en tu Constancia">
                    </div>

                    <div class="input-group">
                        <label for="rfc">RFC *</label>
                        <input type="text" id="rfc" name="rfc" maxlength="13" required
                               value="{{ old('rfc', $datosPrevios->rfc ?? '') }}"
                               style="text-transform:uppercase;" placeholder="Ej. XAXX010101000">
                        <small id="rfc-hint">13 caracteres = persona física · 12 = empresa</small>
                    </div>

                    <div class="input-group">
                        <label for="regimen_fiscal">Régimen Fiscal *</label>
                        <select id="regimen_fiscal" name="regimen_fiscal" required>
                            <option value="">Seleccione...</option>
                            <optgroup label="Persona Física">
                                <option value="605">605 – Sueldos y Salarios</option>
                                <option value="606">606 – Arrendamiento</option>
                                <option value="608">608 – Demás ingresos</option>
                                <option value="611">611 – Ingresos por Dividendos</option>
                                <option value="612">612 – Actividades Empresariales y Profesionales</option>
                                <option value="614">614 – Ingresos por intereses</option>
                                <option value="616">616 – Sin obligaciones fiscales</option>
                                <option value="621">621 – Incorporación Fiscal</option>
                                <option value="625">625 – Plataformas Tecnológicas</option>
                                <option value="626">626 – RESICO</option>
                            </optgroup>
                            <optgroup label="Persona Moral / Empresa">
                                <option value="601">601 – General de Ley Personas Morales</option>
                                <option value="603">603 – Personas Morales Fines no Lucrativos</option>
                                <option value="620">620 – Sociedades Cooperativas</option>
                                <option value="622">622 – Actividades Agrícolas y Ganaderas</option>
                                <option value="623">623 – Grupos de Sociedades</option>
                            </optgroup>
                        </select>
                        <small>Debe coincidir con tu tipo de RFC.</small>
                    </div>

                    <div class="input-group">
                        <label for="codigo_postal">Código Postal Fiscal *</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" maxlength="5" required
                               value="{{ old('codigo_postal', $datosPrevios->codigo_postal ?? '') }}"
                               placeholder="Ej. 76000">
                    </div>

                    <div class="input-group">
                        <label for="uso_cfdi">Uso de CFDI *</label>
                        <select id="uso_cfdi" name="uso_cfdi" required>
                            <option value="">Primero elige tu régimen...</option>
                        </select>
                        <small id="uso-hint">Las opciones dependen de tu régimen fiscal.</small>
                    </div>

                    <div class="input-group">
                        <label for="forma_pago">Forma de Pago *</label>
                        <select id="forma_pago" name="forma_pago" required>
                            <option value="">Seleccione...</option>
                            <option value="01">Efectivo</option>
                            <option value="03">Transferencia electrónica</option>
                            <option value="04">Tarjeta de crédito</option>
                            <option value="28">Tarjeta de débito</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="correo">Correo electrónico *</label>
                        <input type="email" id="correo" name="correo" required
                               value="{{ old('correo', $datosPrevios->correo ?? $reservacion->email_cliente ?? '') }}"
                               placeholder="Para enviarte tu factura">
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-paper-plane"></i> Generar mi Factura
                    </button>

                    <p class="nota">Tu factura se enviará a tu correo en PDF y XML.</p>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/autofactura.js') }}"></script>
</body>
</html>