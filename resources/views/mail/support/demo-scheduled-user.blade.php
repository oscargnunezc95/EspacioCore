<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .highlight { color: #4f46e5; font-weight: bold; }
        .detail-box { background-color: #eef2ff; border: 1px solid #c7d2fe; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-box p { margin: 6px 0; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px; font-size: 16px; }
        .btn-container { text-align: center; margin: 24px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>✅ ¡Tu demo está agendada, {{ explode(' ', $name)[0] }}!</h2>
        </div>

        <p>Gracias por tu interés en <strong class="highlight">EstadoPrisma</strong>. Tu videollamada de demostración ha sido agendada con éxito.</p>

        <div class="detail-box">
            <p><strong>📅 Fecha:</strong> {{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F, Y') }}</p>
            <p><strong>🕐 Hora:</strong> {{ $time }} hrs (Chile)</p>
            <p><strong>🔗 Sala de videollamada:</strong></p>
            <p style="word-break: break-all;">{{ $jitsiLink }}</p>
        </div>

        <p>El día y hora indicados, solo haz clic en el botón de abajo para unirte a la videollamada. No necesitas instalar nada — funciona directamente en tu navegador.</p>

        <div class="btn-container">
            <a href="{{ $jitsiLink }}" class="btn">🎥 Unirse a la videollamada</a>
        </div>

        <p style="font-size: 13px; color: #52525b;">Si tienes dudas antes de la reunión, responde este correo y te ayudaremos.</p>

        <div class="footer">
            EstadoPrisma — Software de gestión para estudios y academias.
        </div>
    </div>
</body>
</html>
