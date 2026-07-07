<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de tu reservación</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333; -webkit-font-smoothing: antialiased;">
    
    <!-- Contenedor principal -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f5f7; padding: 40px 20px;">
        <tr>
            <td align="center">
                
                <!-- Tarjeta del correo -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; max-width: 600px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    
                    <!-- Encabezado / Branding -->
                    <tr>
                        <td style="background-color: #0d2a4b; padding: 35px 24px; text-align: center;">
                            <span style="font-size: 32px; display: block; margin-bottom: 10px;">🚗</span>
                            <span style="color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Viajero Car Rental</span>
                        </td>
                    </tr>
                    
                    <!-- Cuerpo del correo -->
                    <tr>
                        <td style="padding: 40px 32px;">
                            <h1 style="margin: 0 0 16px; font-size: 24px; color: #0d2a4b; font-weight: 700;">¡Tu viaje está a la vuelta de la esquina!</h1>
                            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.6; color: #555555;">
                                Hola<strong>{{ !empty($reservacion->nombre_cliente) ? ' ' . $reservacion->nombre_cliente : '' }}</strong>, este es un recordatorio amistoso de que la fecha de tu reservación está muy cerca. Aquí tienes los detalles:
                            </p>

                            <!-- Caja de detalles de la reservación -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0;">
                                        <p style="margin: 0; font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Folio de Reservación</p>
                                        <p style="margin: 4px 0 0; font-size: 18px; color: #0f172a; font-weight: bold;">{{ $reservacion->codigo }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0;">
                                        <p style="margin: 0; font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Fecha y Hora de Retiro</p>
                                        <p style="margin: 4px 0 0; font-size: 16px; color: #0f172a;">📅 {{ $fechaRetiro->format('d/m/Y') }} &nbsp; ⏰ {{ $fechaRetiro->format('H:i') }} hrs</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px; {{ !empty($reservacion->no_vuelo) ? 'border-bottom:1px solid #e2e8f0;' : '' }}">
                                        <p style="margin: 0; font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Lugar de Retiro</p>
                                        <p style="margin: 4px 0 0; font-size: 16px; color: #0f172a;">📍 {{ $ubicacionRetiro }}</p>
                                    </td>
                                </tr>
                                @if(!empty($reservacion->no_vuelo))
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0; font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">No. de Vuelo</p>
                                        <p style="margin: 4px 0 0; font-size: 16px; color: #0f172a;">✈️ {{ $reservacion->no_vuelo }}</p>
                                    </td>
                                </tr>
                                @endif
                            </table>

                            <!-- Nota importante (Alert Box) -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f7ff; border-left: 4px solid #0056b3; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #0d2a4b;">
                                            <strong>⚠️ Requisitos indispensables para el retiro:</strong><br>
                                            Recuerda presentar tu <strong>identificación oficial</strong>, <strong>licencia de conducir vigente</strong> y la <strong>tarjeta de crédito</strong> del titular.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">
                                Si tienes alguna duda, contáctanos respondiendo a este correo o visitando nuestro sitio web.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                                Este es un mensaje automático, por favor no respondas a este correo.<br>
                                &copy; {{ date('Y') }} Viajero Car Rental. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>