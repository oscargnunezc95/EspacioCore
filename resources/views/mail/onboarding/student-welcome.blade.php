<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-w-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .highlight { color: #0d9488; font-weight: bold; }
        .credentials-box { background-color: #f0fdfa; border: 1px dashed #ccfbf1; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .btn { display: inline-block; background-color: #0f766e; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ explode(' ', $student->name)[0] }}!</h2>
        </div>
        
        <p>¡Te damos la bienvenida oficial a <strong class="highlight">{{ $studio->name }}</strong>!</p>
        
        <p>La administración del estudio ha creado tu ficha de alumna. Ahora cuentas con un <strong>Portal de Alumna</strong> privado donde podrás ver tus próximas clases, revisar tu historial de pagos y explorar nuevos talleres disponibles.</p>

        <div class="credentials-box">
            <p style="margin-top: 0; color: #0f766e; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Tus Credenciales de Acceso</p>
            <p><strong>Email:</strong> {{ $student->email }}</p>
            <p><strong>Contraseña temporal:</strong> {{ $temporaryPassword }}</p>
            
            <a href="{{ route('login') }}" class="btn">Ingresar a mi Portal</a>
        </div>

        <p style="font-size: 13px; color: #52525b;">* Por seguridad, te pedimos que cambies esta contraseña en la sección de configuración de tu perfil la primera vez que ingreses.</p>
        
        <br>
        <p>¡Nos vemos en la próxima clase!</p>
        <p>El equipo de {{ $studio->name }}</p>
    </div>
</body>
</html>