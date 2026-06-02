<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .label { color: #71717a; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .value { color: #18181b; margin-top: 2px; margin-bottom: 16px; }
        .message-box { background-color: #fafafa; border-left: 3px solid #4f46e5; padding: 16px; border-radius: 6px; margin: 20px 0; font-style: italic; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📩 Nueva consulta de soporte</h2>
        </div>

        <p class="label">Nombre</p>
        <p class="value">{{ $name }}</p>

        <p class="label">Email</p>
        <p class="value">{{ $email }}</p>

        @if($body)
        <p class="label">Mensaje</p>
        <div class="message-box">
            {{ $body }}
        </div>
        @endif

        <div class="footer">
            Este correo fue generado desde el formulario de soporte de EstadoPrisma.
        </div>
    </div>
</body>
</html>
