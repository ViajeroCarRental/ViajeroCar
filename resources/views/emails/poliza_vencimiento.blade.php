<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">
  <style>
    body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table, td { border-collapse:collapse !important; }
    body { margin:0 !important; padding:0 !important; width:100% !important; }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background-color: #f4f6fb;
      color: #333333;
    }

    .container {
      max-width: 700px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      border: 1px solid #e2e8f0;
    }

    /* HEADER */
    .header {
      background: linear-gradient(90deg, #E50914, #b1060f);
      text-align: center;
      padding: 35px 25px;
      color: #ffffff;
    }

    .header img {
      width: 150px;
      margin-bottom: 15px;
      border: none;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .header h1 {
      font-size: 26px;
      font-weight: 600;
      margin: 0;
      letter-spacing: 0.3px;
    }

    /* STATUS */
    .status {
      text-align: center;
      margin: 35px 0 15px;
    }

    .status span {
      display: inline-block;
      font-weight: 600;
      font-size: 17px;
      padding: 12px 30px;
      border-radius: 50px;
      letter-spacing: 0.3px;
      border: 2px solid transparent;
    }

    .status-warning {
      background: #fff5e5;
      color: #b45309;
      border-color: #fbbf24;
    }

    .status-danger {
      background: #ffe2e2;
      color: #b91c1c;
      border-color: #ef4444;
    }

    /* BODY */
    .content {
      padding: 0 40px 40px;
      color: #2b2b2b;
      line-height: 1.7;
    }

    .info {
      background: #fff2f2;
      border-left: 5px solid #E50914;
      border-radius: 10px;
      padding: 20px 25px;
      margin: 25px 0;
    }

    .info p {
      margin: 6px 0;
      font-size: 15px;
    }

    .divider {
      height: 1px;
      background: #e2e8f0;
      margin: 30px 0;
    }

    .note {
      text-align: center;
      color: #555;
      font-size: 14px;
      line-height: 1.6;
    }

    /* FOOTER */
    .footer {
      background: #fafafa;
      color: #777;
      font-size: 13px;
      text-align: center;
      padding: 20px 30px;
      border-top: 1px solid #e2e8f0;
    }

    .footer a {
      color: #E50914;
      text-decoration: none;
      font-weight: 500;
    }

    /* RESPONSIVE */
    @media screen and (max-width: 600px) {
      .content { padding: 0 25px 30px; }
      .header h1 { font-size: 22px; }
      .info { padding: 18px; }
    }

    /* DARK MODE */
    @media (prefers-color-scheme: dark) {
      body {
        background-color: #121212 !important;
        color: #f4f4f4 !important;
      }
      .container {
        background-color: #1e1e1e !important;
        border: 1px solid #333 !important;
        box-shadow: none !important;
      }
      .header {
        background: linear-gradient(90deg, #E50914, #ff4d5a) !important;
        color: #ffffff !important;
      }
      .info {
        background: rgba(229, 9, 20, 0.12) !important;
        border-left: 4px solid #ff4d5a !important;
      }
      .status-warning {
        background: rgba(255, 183, 3, 0.1) !important;
        color: #facc15 !important;
        border-color: #facc15 !important;
      }
      .status-danger {
        background: rgba(229, 9, 20, 0.15) !important;
        color: #ff7676 !important;
        border-color: #ff4d5a !important;
      }
      .footer {
        background: #181818 !important;
        color: #ccc !important;
        border-top-color: #333 !important;
      }
      .footer a {
        color: #ff4d5a !important;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- HEADER -->
    <div class="header">
      <img src="https://i.ibb.co/rR3WvFNf/image.png" alt="Viajero Car Rental" width="150" style="display:block;margin:auto;border:none;">
      <h1>Notificación de Póliza</h1>
    </div>

    <!-- CONTENT -->
    <div class="content">
      <div class="status">
        @if($diasRestantes < 0)
          <span class="status-danger">🚨 Póliza vencida</span>
        @else
          <span class="status-warning">⚠️ Póliza próxima a vencer</span>
        @endif
      </div>

      <div class="info">
        <p><strong>Vehículo:</strong> {{ $vehiculo->nombre_publico }} ({{ $vehiculo->placa }})</p>
        <p><strong>Aseguradora:</strong> {{ $vehiculo->aseguradora }}</p>
        <p><strong>Fin de vigencia:</strong> {{ \Carbon\Carbon::parse($vehiculo->fin_vigencia_poliza)->format('d/m/Y') }}</p>
        <p><strong>Días restantes:</strong> {{ round($diasRestantes) }}</p>
      </div>

      <div class="divider"></div>

      <p class="note">
        Este mensaje ha sido generado automáticamente por el sistema de <strong>Viajero Car Rental</strong>.<br>
        Verifique la vigencia de su póliza para mantener la cobertura activa del vehículo.
      </p>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      © {{ date('Y') }} <strong>Viajero Car Rental</strong> — Todos los derechos reservados.<br>
      <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
      <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
    </div>
  </div>
</body>
</html>
